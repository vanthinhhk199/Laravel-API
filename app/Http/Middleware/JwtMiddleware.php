<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::toUser($request->input('token'));
            $request->merge(['user' => $user]);
        } catch (\Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['error' => 'Token is Invalid']);
            } else if ($e instanceof TokenExpiredException){
                return response()->json(['error' => 'Token is Expired']);
            } else {
                return response()->json(['error' => 'Something is wrong']);
            }
        }

        return $next($request);
    }
}
