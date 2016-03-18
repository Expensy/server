<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Project extends ApiModel
{
  use SoftDeletes;

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'projects';

  protected $fillable = ['title'];
  
  protected $rulesForCreation = [
      'title' => ['required']
  ];
  protected $rulesForUpdate = [
      'title' => ['required']
  ];

  public function users()
  {
    return $this->belongsToMany('App\Models\User');
  }

  public function categories()
  {
    return $this->hasMany('App\Models\Category');
  }

  public function entries()
  {
    return $this->hasMany('App\Models\Entry');
  }

  public function isAccessibleByConnectedUser()
  {
    $connectedUserId = Auth::user()->id;

    return $this->users->contains(function ($index, $user) use ($connectedUserId) {
      return $user->id === $connectedUserId;
    });
  }
}
