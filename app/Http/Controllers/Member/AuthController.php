<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'only' => [
                'logout',
            ],
        ]);

        $this->middleware('account_check', [
            'only' => [
                'logout',
            ],
        ]);
    }

    public function register(Request $request)
    {
        $validationRules =  [
            'full_name' => 'required|between:5,100',
            # (62)  : first 2 digit must be "62"
            # \d+$  : remaining character must be number
            # 27 char max, because remaining 3 digit for "(","+",")" and total will be 30 digit. example: (+62).....
            'phone_number' => 'required|max:27|regex:/(62)\d+$/',
            'shirt_size' => 'required|max:10',
            'email_address' => 'required|email|max:100',
            'current_address' => 'required',
            'password' => 'required|min:6',
        ];
        $errors = $this->staticValidation($request->all(), $validationRules);
        if (count($errors) > 0) {
            $respMessage = $errors->first();
            return $this->respondWithMissingField($respMessage);
        }

        $full_name = $request->input('full_name');
        $phone_number = $request->input('phone_number');
        # Check if first 2 digit is "62"
        if (substr($phone_number, 0, 2) == 62) {
            # Replace "62" with (+62)
            $phone_number = substr($phone_number, 2);
            $phone_number = "(+62)$phone_number";
        } else {
            $respMessage = trans('messages.ValueMustBeValidPhoneNumber');
            return $this->respondFailedWithMessage($respMessage);
        }
        $email_address = strtolower($request->input('email_address'));
        $current_address = $request->input('current_address');
        $shirt_size = $request->input('shirt_size');
        $password = Hash::make($request->input('password'));

        DB::beginTransaction();
        try {
            # Check Email & Phone
            $member = DB::table('member')
                ->where('email_address', $email_address)
                ->orWhere('phone_number', $phone_number);
            if ($member->get()->count() > 0) {
                if ($member->get()->count() == 1) {
                    if ($member->first()->email_address == $email_address) {
                        DB::rollback();
                        $respMessage = trans('messages.EmailAddressAlreadyRegistered');
                        return $this->respondFailedWithMessage($respMessage);
                    } else if ($member->first()->phone_number == $phone_number) {
                        DB::rollback();
                        $respMessage = trans('messages.PhoneNumberAlreadyRegistered');
                        return $this->respondFailedWithMessage($respMessage);
                    } else {
                        DB::rollback();
                        $respMessage = trans('messages.StatusNotIdentified');
                        return $this->respondFailedWithMessage($respMessage);
                    }
                } else {
                    DB::rollback();
                    $respMessage = trans('messages.DataRegisteredMoreThanOnce');
                    return $this->respondFailedWithMessage($respMessage);
                }
            } else {
                $phone_number_verify_status = 'NOT VERIFIED';
                $email_address_verify_status = 'NOT VERIFIED';
                $position = 'MEMBER';
                $account_status = 'ACTIVE';
                $payment_status = 'INACTIVE';

                # Insert
                $valueDB = [
                    'full_name' => $full_name,
                    'phone_number' => $phone_number,
                    'email_address' => $email_address,
                    'current_address' => $current_address,
                    'shirt_size' => $shirt_size,
                    'password' => $password,
                    'phone_number_verify_status' => $phone_number_verify_status,
                    'email_address_verify_status' => $email_address_verify_status,
                    'position' => $position,
                    'account_status' => $account_status,
                    'payment_status' => $payment_status,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                # Insert to Database and get ID in return
                $id = DB::table('member')->insertGetId($valueDB);

                # Update
                $affected = DB::table('member')
                    ->where('id', $id)
                    ->update([
                        'code' => "MBR-$id",
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                if ($affected != 1) {
                    DB::rollback();
                    $respMessage = trans('messages.UpdateDataFailed');
                    return $this->respondFailedWithMessage($respMessage);
                } else {
                    DB::commit();
                    $respMessage = trans('messages.ProccessSuccess');
                    return $this->respondSuccessWithMessageAndData($respMessage);
                }
            }
        } catch (\Exception $e) {
            $this->sendApiErrorToTelegram($request->fullUrl(), $request->header(), $request->all(), $e->getMessage());

            DB::rollback();
            $respMessage = trans('messages.ChangeCannotBeDone');
            return $this->respondFailedWithMessage($respMessage);
        }
    }

    public function login(Request $request)
    {
        $validationRules =  [
            'email_or_phone' => 'required',
            'password' => 'required',
        ];
        $errors = $this->staticValidation($request->all(), $validationRules);
        if (count($errors) > 0) {
            $respMessage = $errors->first();
            return $this->respondWithMissingField($respMessage);
        }

        $email_or_phone = strtolower($request->input('email_or_phone'));
        $password = $request->input('password');

        DB::beginTransaction();
        try {
            # Select
            $member = DB::table('member')
                ->where('email_address', $email_or_phone)
                ->orWhere('phone_number', $email_or_phone);

            if ($member->get()->count() == 0) {
                DB::rollback();
                $respMessage = trans('messages.MemberAccountNotFound');
                return $this->respondFailedWithMessage($respMessage);
            } else if ($member->get()->count() > 1) {
                DB::rollback();
                $respMessage = trans('messages.MemberRegisteredMoreThanOnce');
                return $this->respondFailedWithMessage($respMessage);
            } else {
                if (Hash::check($password, $member->value('password')) == false) {
                    DB::rollback();
                    $respMessage = trans('messages.InvalidAccountPassword');
                    return $this->respondFailedWithMessage($respMessage);
                }
            }

            $api_token = Crypt::encrypt(Str::random(40));

            # Update
            $affected = $member->update([
                'api_token' => $api_token,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            if ($affected != 1) {
                DB::rollback();
                $respMessage = trans('messages.UpdateDataFailed');
                return $this->respondFailedWithMessage($respMessage);
            } else {
                DB::commit();
                $respMessage = trans('messages.ProccessSuccess');
                $respData = $member->select([
                    'id',
                    'full_name',
                    'position',
                    'account_status',
                    'payment_status',
                    'phone_number_verify_status',
                    'email_address_verify_status',
                    'api_token',
                ])->first();
                return $this->respondSuccessWithMessageAndData($respMessage, $respData);
            }
        } catch (\Exception $e) {
            $this->sendApiErrorToTelegram($request->fullUrl(), $request->header(), $request->all(), $e->getMessage());

            DB::rollback();
            $respMessage = trans('messages.ChangeCannotBeDone');
            return $this->respondFailedWithMessage($respMessage);
        }
    }

    public function logout(Request $request)
    {
        $idToken = $request->header('Authorization');
        $idToken = explode(' ', $idToken);

        if (count($idToken) == 3) {
            DB::beginTransaction();
            try {
                # Update
                $affected = DB::table('member')
                    ->where([
                        'id' => $idToken[1],
                        'api_token' => $idToken[2],
                    ])
                    ->update([
                        'api_token' => null,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                if ($affected != 1) {
                    DB::rollback();
                    $respMessage = trans('messages.UpdateDataFailed');
                    return $this->respondFailedWithMessage($respMessage);
                } else {
                    DB::rollback();
                    $respMessage = trans('messages.ProccessSuccess');
                    return $this->respondSuccessWithMessageAndData($respMessage);
                }
            } catch (\Exception $e) {
                $this->sendApiErrorToTelegram($request->fullUrl(), $request->header(), $request->all(), $e->getMessage());

                DB::rollback();
                $respMessage = trans('messages.ChangeCannotBeDone');
                return $this->respondFailedWithMessage($respMessage);
            }
        }

        $respMessage = trans('messages.Error');
        return $this->respondFailedWithMessage($respMessage);
    }
}
