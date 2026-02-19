<?php

namespace App\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        try {
            // Try to authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 401);
            }
            
            // Set the authenticated user for the request
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token has expired'
            ], 401);
            
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token is invalid'
            ], 401);
            
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token not provided'
            ], 401);
        }
        
        return $next($request);
    }
}
