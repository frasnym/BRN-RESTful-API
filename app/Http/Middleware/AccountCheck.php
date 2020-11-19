<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Member;

class AccountCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('Authorization')) {
            $idToken = $request->header('Authorization'); // ? api_token on header
            $idToken = explode(' ', $idToken);

            if (count($idToken) == 3) {
                # Catch member object, selected from AuthMiddleware
                $member = $request->member;
                // $member = Member::where(['id' => $idToken[1], 'api_token' => $idToken[2]])->first();

                if ($member->account_status != 'ACTIVE') {
                    $response = [
                        'success' => false,
                        'message' => trans('messages.MemberAccountInactive'),
                    ];
                    return response($response, 401);
                }
            }
        }
        return $next($request);
    }
}
