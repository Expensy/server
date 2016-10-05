<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\AuthRepository;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
  protected $authRepository;

  public function __construct(AuthRepository $authRepository) {
    $this->authRepository = $authRepository;
  }

  public function authenticate(Request $request) {
    $credentials = $request->only('email', 'password');

    $token = $this->authRepository->authenticate($credentials);

    if (!$token) {
      return $this->respondUnauthorized();
    }

    $user = User::where('email', $credentials['email'])->first();
    if (!$user || $user->confirmation_token != null) {
      return $this->respondUnauthorized('Account not validated');
    }

    return $this->respond([
      'token' => $token
    ]);
  }
}
