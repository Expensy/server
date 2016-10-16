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

  protected $fillable = ['title', 'currency'];

  protected $rulesForCreation = [
    'title' => ['required', 'unique_project_name'],
    'currency' => ['required', 'currency'],
  ];
  protected $rulesForUpdate = [
    'title' => ['required', 'unique_project_name'],
    'currency' => ['required', 'currency'],
  ];

  public function users() {
    return $this->belongsToMany('App\Models\User')->orderBy('last_name', 'asc');
  }

  public function categories() {
    return $this->hasMany('App\Models\Category')->orderBy('title', 'asc');
  }

  public function entries() {
    return $this->hasMany('App\Models\Entry')->orderBy('date', 'desc');
  }

  public function isAccessibleByConnectedUser() {
    $connectedUserId = Auth::user()->id;

    return collect($this->users->all())->pluck('id')->contains($connectedUserId);
  }
}
