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
      'title' => ['required', 'unique_project_name']
  ];
  protected $rulesForUpdate = [
      'title' => ['required', 'unique_project_name']
  ];

  public function users()
  {
    return $this->belongsToMany('App\Models\User')->orderBy('name', 'asc');
  }

  public function categories()
  {
    return $this->hasMany('App\Models\Category')->orderBy('title', 'asc');
  }

  public function entries()
  {
    return $this->hasMany('App\Models\Entry')->orderBy('date', 'desc');
  }

  public function isAccessibleByConnectedUser()
  {
    $connectedUserId = Auth::user()->id;

    return $this->users->contains(function ($index, $user) use ($connectedUserId) {
      return $user->id === $connectedUserId;
    });
  }
}
