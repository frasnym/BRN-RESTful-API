<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        // $this->middleware('age', [
            // 'only' => [
                // 'generateKey',
            // ],
            // 'except' => [
            //     'generateKey',
            // ]
        // ]);
    }

    public function generateKey()
    {
        return $this->curlRequest('https://api.jayamaheeasyride.com/Master/promo_list/', 'GET', [], []);
        // return 'Hello';
    }

    public function getProfile()
    {
        return "getProfile: " . route('profile.action');
    }

    public function getProfileAction()
    {
        return "getProfileAction: " . route('profile');
    }

    public function fooBar(Request $request)
    {
        return $request->path();
    }

    public function getUser(Request $request)
    {
        // $inputParam["name"] = $request->name;
        $inputParam["name"] = $request->input('name', 'Default Name');

        // if ($request->has('name')) {
        //     return "Success";
        // } else return "Failed";

        // if ($request->has(['name', 'email'])) {
        //     return "Success";
        // } else return "Failed";

        // return $request->all();
        // return $request->only(['key']);
        // return $request->except(['key']);

        return $inputParam;
    }

    public function response() {
        // return response('Content', 201);
        // return response('Content', 201)->header('Content-Type', 'application/json');

        $data = ['status' => 'Success'];
        // return response($data, 201)->header('Content-Type', 'application/json');
        // return response($data, 201)->header()->header();
        // return response($data, 201);
        return response()->json([
            'status' => 0,
            'message' => 'Success'
        ], 201);
    }
}
