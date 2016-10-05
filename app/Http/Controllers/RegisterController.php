<?php

namespace App\Http\Controllers;

use App\Models\User;

class RegisterController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct() {
    $this->middleware('guest');
  }

  public function confirm($id, $token) {
    $user = User::where('id', $id)->where('confirmation_token', $token)->first();
    if ($user) {
      $user->update(['confirmation_token' => null]);

      return view('confirm')->with('success', true);
    }

    return view('confirm')->with('success', false);
  }
}
