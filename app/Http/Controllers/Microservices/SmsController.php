<?php

namespace App\Http\Controllers\Microservices;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmsController extends Controller
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

    public function send_inquiry_sms(Request $request)
    {
        DB::beginTransaction();
        try {
            # Select table sms_outbox
            $sms_outbox = DB::table('sms_outbox')
                ->where('status', 'INQUIRY')
                ->orderBy('id', 'asc')
                ->take(5);

            $respOutput = [];
            $response = '';
            foreach ($sms_outbox->get() as $key => $value) {

                # Trying to send sms
                try {
                    # Create required variable used for send sms
                    $url = "http://server.dutasms.com:8080/web2sms/api/SendSMS.aspx";
                    // $url = "http://server.yuksms.com:8080/web2sms/api/SendSMS.aspx";
                    $type = 'GET';
                    $headers = [];
                    $body = [];

                    # DutaSMS Trial
                    $message = $value->message;
                    $user = "sms";
                    $password = "123456";
                    $auth = MD5($user . $password . $value->recipient);
                    $message = urlencode($message);
                    $url .= "?username=" . $user . "&mobile=" . $value->recipient . "&message=" . $message . "&auth=" . $auth;

                    $curlRequest = $this->curlRequest($url, $type, $headers, $body);
                    $code_response = substr($curlRequest, 0, 4);
                    $code_description = [
                        "1701" => "Success",
                        "1702" => "Invalid Username or Password",
                        "1703" => "Internal Server Error",
                        "1704" => "Data not found",
                        "1705" => "Process Failed",
                        "1706" => "Invalid Message",
                        "1707" => "Invalid Number",
                        "1708" => "Insufficient Credit",
                        "1709" => "Group Empty",
                        "1711" => "Invalid Group Name ",
                        "1712" => "Invalid Group ID ",
                        "1713" => "Invalid msgid",
                        "1721" => "Invalid Phonebook Name ",
                        "1722" => "Invalid Phonebook ID",
                        "1731" => "User Name already exist",
                        "1732" => "Sender ID not valid",
                        "1733" => "Internal Error – please contact administrator ",
                        "1734" => "Invalid client user name",
                        "1735" => "Invalid Credit Value",
                    ];
                    $response = $code_response . "[" . $code_description[$code_response] . "] " . ": " . $curlRequest;

                    if ($code_response == "1701") {
                        $success = true;
                        $status = "SUCCESS";
                    } else {
                        $success = false;
                        $status = "FAILED";
                    }

                } catch (\Exception $e) {
                    # Catch error if failed send sms
                    $response = $e->getMessage();
                    $success = false;
                    $status = 'FAILED';

                    $this->sendApiErrorToTelegram($request->fullUrl(), $request->header(), $request->all(), $response);
                }

                # Push result to JSON
                array_push($respOutput, [
                    'recipient' => $value->recipient,
                    'success' => $success,
                    'response' => $response,
                ]);

                # Update sms_outbox: set status & response based on $status & $response
                $affected = DB::table('sms_outbox')
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
