<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends ApiController
{
  public function __construct()
  {
  }

  public function authenticate(Request $request)
  {
    $credentials = $request->only('email', 'password');

    try {
      if (!$token = JWTAuth::attempt($credentials)) {
        return $this->respondForbidden();
      }
    } catch (JWTException $e) {
      return $this->respondInternalError('Can not create token');
    }

    return $this->respond([
        'token' => $token
    ]);
  }
}