<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;

class UserMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  \Closure                 $next
   *
   * @return mixed
   */
  public function handle($request, Closure $next) {
    $user = JWTAuth::parseToken()->toUser();

    if (!$user || $user->confirmation_token != null) {
      return response()->json(['message' => 'Account not validated'], 401);
    }

    return $next($request);
  }
}
