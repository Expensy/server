<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends ApiModel
{
  use SoftDeletes;

  protected $dates = ['deleted_at'];

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'projects';

  protected $fillable = ['title'];

  protected $commonRules = [
      'title' => 'required'
  ];

  protected $rulesForCreation = [];
  protected $rulesForUpdate = [
      'id' => 'required'
  ];

  public function users()
  {
    return $this->belongsToMany('App\Models\User');
  }
}
