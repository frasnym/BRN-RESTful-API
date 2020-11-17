<?php

namespace App\Http\Controllers\Microservices;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
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

    public function check_expiry(Request $request)
    {
        DB::beginTransaction();
        try {
            $respOutput = [
                'key' => 0,
                'order' => 0,
            ];

            # Set Key EXPIRED with passed "expired_time"
            $affected = DB::table('key_user')
                ->where([
                    'status' => 'ACTIVE',
                ])
                ->where('expired_time', '<', date('Y-m-d H:i:s'))
                ->update([
                    'status' => 'EXPIRED',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $respOutput['key'] = $affected;

            # Select table order_payment
            $order_payment = DB::table('order_payment')
                ->where('status', 'INQUIRY')
                ->where('expiry_date', '<', date('Y-m-d H:i:s'))
                ->orderBy('id', 'asc');

            foreach ($order_payment->get() as $key => $value) {

                # Update coresponding order data
                $affected = DB::table('order')
                    ->where([
                        'id' => $value->order_id,
                        'payment_method_id' => $value->payment_method_id,
                        'status' => 'REQPAYMENT',
                    ])
                    ->update([
                        'status' => 'EXPIRED',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                $respOutput['order'] += $affected;
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
