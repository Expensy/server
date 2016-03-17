<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends ApiModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
  use Authenticatable, Authorizable, CanResetPassword;

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'users';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'name', 'email', 'password',
  ];

  /**
   * The attributes excluded from the model's JSON form.
   *
   * @var array
   */
  protected $hidden = [
      'password', 'remember_token',
  ];

  protected $commonRules = [
      'email'    => ['email'],
      'password' => ['confirmed']
  ];

  protected $rulesForCreation = [
      'email'    => ['required', 'unique:users,email'],
      'password' => ['required']
  ];

  protected $rulesForUpdate = [
      'email'        => ['unique:users,email,{id}'],
      'password_old' => ['old_password', 'required_with:password']
  ];

  public function projects()
  {
    return $this->belongsToMany('App\Models\Project');
  }
}
