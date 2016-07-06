<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Repositories\AuthRepository;
use Illuminate\Http\Request;

class AuthController extends ApiController {
  protected $authRepository;

  public function __construct(AuthRepository $authRepository) {
    $this->authRepository = $authRepository;
  }

  public function authenticate(Request $request) {
    $credentials = $request->only('email', 'password');

    $token = $this->authRepository->authenticate($credentials);

    if (!$token) {
      return $this->respondForbidden();
    }

    return $this->respond([
      'token' => $token
    ]);
  }
}
