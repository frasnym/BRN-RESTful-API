<?php

namespace App\Http\Controllers\Member;

use App\Models\Member;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class AccountController extends BaseController
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $result = Member::all();
        return response($result);
    }

    public function show($id)
    {
        $result = Member::find($id);

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $data = new Member();
        $data->activity = $request->input('activity');
        $data->description = $request->input('description');
        $data->save();

        return response('Berhasil Tambah Data');
    }

    public function update(Request $request, $id){
        $data = Member::where('id',$id)->first();
        $data->activity = $request->input('activity');
        $data->description = $request->input('description');
        $data->save();

        return response('Berhasil Merubah Data');
    }

    public function destroy($id){
        $data = Member::where('id',$id)->first();
        $data->delete();

        return response('Berhasil Menghapus Data');
    }

    //
}
