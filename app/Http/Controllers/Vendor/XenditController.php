<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class XenditController extends Controller
{
    public function invoice(Request $request)
    {
        $this->sendApiErrorToTelegram($request->fullUrl(), $request->header(), $request->all(), 'Xendit Invoice');

        # Request BODY validation
        $validationRules =  [
            'external_id' => 'required',
            'status' => 'required',
        ];
        $errors = $this->staticValidation($request->all(), $validationRules);
        if (count($errors) > 0) {
            $respMessage = $errors->first();
            return $this->respondWithMissingField($respMessage);
        };
        $invoice = strtoupper($request->input('external_id'));
        $status = strtoupper($request->input('status'));

        $header = $request->header();

        if ($header['x-callback-token'][0] == env('XENDIT_CALLBACK_TOKEN')) {

            DB::beginTransaction();
            try {

                # Check Invoice on table "order"
                $order = DB::table('order')
                    ->where([
                        'invoice' => $invoice,
                    ]);

                if ($order->get()->count() == 0) {
                    DB::commit();
                    $respMessage = trans('messages.OrderNotFound');
                    return $this->respondFailedWithMessage($respMessage);
                } else if ($order->get()->count() > 1) {
                    DB::commit();
                    $respMessage = trans('messages.OrderRegisteredMoreThanOnce');
                    return $this->respondFailedWithMessage($respMessage);
                }

                # Object "order" with all column selected
                $order = $order->first();

                if ($status == 'PAID' || $status == 'EXPIRED') {

                    # Update table "order" change "status" to "$status"
                    $columnUpdate = [
                        'status' => $status,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    if ($status == 'PAID') $columnUpdate['date_payment'] = date('Y-m-d H:i:s', strtotime($request->input('paid_at')));

                    $affected = DB::table('order')
                        ->where([
                            'id' => $order->id,
                        ])
                        ->update($columnUpdate);
                    if ($affected != 1) {
                        DB::rollback();
                        $respMessage = trans('messages.UpdateDataFailed1');
                        return $this->respondFailedWithMessage($respMessage);
                    }

                    # Update table "order_payment" change "status" to "$status"
                    $callback = [
                        'header' => $header,
                        'body' => $request->all(),
                    ];
                    $affected = DB::table('order_payment')
                        ->where([
                            'order_id' => $order->id,
                            'payment_method_id' => 3, //? 3 is Invoice
                        ])
                        ->update([
                            'status' => $status,
                            'callback' => json_encode($callback),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    if ($affected != 1) {
                        DB::rollback();
                        $respMessage = trans('messages.UpdateDataFailed2');
                        return $this->respondFailedWithMessage($respMessage);
                    }

                    DB::commit();
                    $respMessage = trans('messages.ProccessSuccess');
                    return $this->respondSuccessWithMessageAndData($respMessage);
                } else {
                    $this->sendApiErrorToTelegram($request->fullUrl(), $request->header(), $request->all(), 'Xendit Invoice Error');

                    # Update table "order" change "status" to "CANCEL"
                    $affected = DB::table('order')
                        ->where([
                            'id' => $order->id,
                        ])
                        ->update([
                            'status' => 'CANCEL',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    if ($affected != 1) {
                        DB::rollback();
                        $respMessage = trans('messages.UpdateDataFailed');
                        return $this->respondFailedWithMessage($respMessage);
                    }

                    # Update table "order_payment" change "status" to "ERROR"
                    $callback = [
                        'header' => $header,
                        'body' => $request->all(),
                    ];
                    $affected = DB::table('order_payment')
                        ->where([
                            'id' => $order->id,
                            'payment_method_id' => 3, //? 3 is Invoice
                        ])
                        ->update([
                            'status' => $status,
                            'callback' => json_encode($callback),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    if ($affected != 1) {
                        DB::rollback();
                        $respMessage = trans('messages.UpdateDataFailed');
                        return $this->respondFailedWithMessage($respMessage);
                    }

                    DB::rollback();
                    $respMessage = trans('messages.StatusNotIdentified');
                    return $this->respondFailedWithMessage($respMessage);
                }
            } catch (\Exception $e) {
                $this->sendApiErrorToTelegram($request->fullUrl(), $request->header(), $request->all(), $e->getMessage());

                DB::rollback();
                $respMessage = trans('messages.ChangeCannotBeDone');
                return $this->respondFailedWithMessage($respMessage);
            }
        } else {
            $respMessage = trans('messages.TokenVerificationDidNotMatch');
            return $this->respondFailedWithMessage($respMessage);
        }

        $respMessage = trans('messages.Error');
        return $this->respondFailedWithMessage($respMessage);
    }
}
