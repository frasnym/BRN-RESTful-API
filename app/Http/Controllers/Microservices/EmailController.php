<?php

namespace App\Http\Controllers\Microservices;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function send_inquiry_email(Request $request)
    {
        DB::beginTransaction();
        try {
            # Select table email_outbox
            $email_outbox = DB::table('email_outbox')
                ->where('status', 'INQUIRY')
                ->orderBy('id', 'asc')
                ->take(5);

            $respOutput = [];
            $response = '';
            foreach ($email_outbox->get() as $key => $value) {

                # Trying to send email
                try {
                    # Create required object used for mail data
                    $user = new \stdClass();
                    $user->email = $value->recipient;
                    $user->subject = $value->subject;
                    $user->sender = $value->sender;
                    $user->sender_name = "BRN Mailer no-reply";

                    # Send email
                    Mail::html($value->body, function ($mail) use ($user) {
                        $mail->to($user->email)
                            ->subject($user->subject)
                            ->from($user->sender, $user->sender_name);
                    });
                    $success = true;
                    $response = 'SUCCESS';
                    $status = 'SUCCESS';
                } catch (\Exception $e) {
                    # Catch error if failed send email
                    $response = $e->getMessage();
                    $success = false;
                    $status = 'FAILED';

                    $this->sendApiErrorToTelegram($request->fullUrl(), $request->header(), $request->all(), $response);
                }

                # Push result to JSON
                array_push($respOutput, [
                    'sender' => $value->sender,
                    'recipient' => $value->recipient,
                    'success' => $success,
                    'response' => $response,
                ]);

                # Update email_outbox: set status & response based on $status & $response
                $affected = DB::table('email_outbox')
                    ->where('id', $value->id)
                    ->update([
                        'status' => $status,
                        'response' => $response,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                
                # Check if success updating DB
                if ($affected != 1) {
                    DB::rollback();
                    $respMessage = trans('messages.UpdateDataFailed');
                    return $this->respondFailedWithMessageAndData($respMessage, $respOutput);
                }

                # Stop email service if failed
                if (!$success) {
                    DB::commit();
                    $respMessage = trans('messages.ProccessSuccess');
                    return $this->respondSuccessWithMessageAndData($respMessage, $respOutput);
                }
            }

            DB::commit();
            $respMessage = trans('messages.ProccessSuccess');
            return $this->respondSuccessWithMessageAndData($respMessage, $respOutput);
        } catch (\Exception $e) {
            $this->sendApiErrorToTelegram($request->fullUrl(), $request->header(), $request->all(), $e->getMessage());

            DB::rollback();
            $respMessage = trans('messages.ChangeCannotBeDone');
            return $this->respondFailedWithMessage($respMessage);
        }

        $respMessage = trans('messages.Error');
        return $this->respondFailedWithMessage($respMessage);
    }
}
