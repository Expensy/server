<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;

class User extends ApiModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
  use Authenticatable, Authorizable, CanResetPassword, Notifiable;

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
    'first_name', 'last_name', 'email', 'password', 'confirmation_token'
  ];

  /**
   * The attributes excluded from the model's JSON form.
   *
   * @var array
   */
  protected $hidden = [
    'password', 'remember_token',
  ];

  protected $rulesForCreation = [
    'first_name' => ['required'],
    'last_name' => ['required'],
    'email' => ['required', 'email', 'unique:users,email'],
    'password' => ['required', 'confirmed']
  ];

  protected $rulesForUpdate = [
    'first_name' => ['required'],
    'last_name' => ['required'],
    'email' => ['required', 'email', 'unique:users,email,{id}'],
    'password_old' => ['old_password', 'required_with:password']
  ];

  protected $errorMessages = [
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute must be a valid email address',
    'unique' => 'The :attribute must be unique',
    'confirmed' => 'The :attribute must be confirmed',
    'old_password' => 'The :attribute is incorrect'
  ];

  public function projects() {
    return $this->belongsToMany('App\Models\Project')->orderBy('title', 'asc');
  }
}
