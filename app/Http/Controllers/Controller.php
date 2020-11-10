<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    /**
     * @param $url
     * @param $type
     * @param $headers
     * @param $body
     * @return json
     */
    protected function curlRequest($url, $type, $headers, $body)
    {
        $config['useragent'] = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $config['useragent']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, count($body));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     *
     * @param $url
     * @param $header
     * @param $body
     * @param $error
     * @return void
     */
    protected function sendApiErrorToTelegram($url, $header, $body, $error)
    {
        $TELEGRAM_BOT_ID = env('TELEGRAM_BOT_ID');
        $TELEGRAM_CHAT_ID = env('TELEGRAM_CHAT_ID');

        $message = "Full URL: $url";
        $message .= "\n\nHeaders\n" . json_encode($header);
        $message .= "\n\nBody\n" . json_encode($body);
        $message .= "\n\nError Message\n" . json_encode($error);
        $message = urlencode($message);

        try {
            file_get_contents("https://api.telegram.org/bot$TELEGRAM_BOT_ID:AAFnYpbUBhYB2LKbto5Cg_osgYjlCwv4Jx0/sendMessage?chat_id=$TELEGRAM_CHAT_ID&text=" . $message . "&parse_mode=html");
        } catch (\Throwable $th) {
            $message = "Full URL: $url";
            $message .= "\n\nHeaders\n" . json_encode($header);
            $message .= "\n\nBody\n" . json_encode($body);
            $message .= "\n\nError Message: Failed Send Error to Telegram";
            $message = urlencode($message);
            file_get_contents("https://api.telegram.org/bot$TELEGRAM_BOT_ID:AAFnYpbUBhYB2LKbto5Cg_osgYjlCwv4Jx0/sendMessage?chat_id=$TELEGRAM_CHAT_ID&text=" . $message . "&parse_mode=html");
        }

        return;
    }

    /**
     * @param $input
     * @param $rules
     * @return errors
     */
    protected function staticValidation($input, $rules)
    {
        $messages = [
            'required' => trans('messages.ParameterValueRequired_Attribute'),
            'email' => trans('messages.PleaseProvideAValidEmail_Attribute'),
            'between' => trans('messages.CharactersLongMustBeBetweenMinAndMax_Attribute'),
            'max' => trans('messages.CharactersLongMustBeMaximumMaxDigit_Attribute'),
            'min' => trans('messages.CharactersLongMustBeMinimunMinDigit_Attribute'),
            'ip' => trans('messages.ValueMustBeValidIPAddress_Attribute'),
        ];
        $validator = Validator::make($input, $rules, $messages);
        return $validator->errors();
    }

    /**
     * @param $message
     * @return Response
     */
    protected function respondWithMissingField($message)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 400);
    }

    /**
     * @param $message
     * @param $data
     * @return Response
     */
    protected function respondSuccessWithMessageAndData($message, $data = null)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];
        $data ? $response['data'] = $data : null;

        return response()->json(
            $response,
            200
        );
    }

    /**
     * @param $message
     * @return Response
     */
    protected function respondFailedWithMessage($message)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 400);
    }
}
