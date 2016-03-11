<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends ApiModel
{
  use SoftDeletes;

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'categories';

  protected $fillable = ['title', 'color', 'project_id'];

  protected $commonRules = [
      'title'      => ['required'],
      'color'      => ['required'],
      'project_id' => ['required', 'exists:projects,id'],
  ];

  protected $rulesForCreation = [];
  protected $rulesForUpdate = [];

  public function project()
  {
    return $this->belongsTo('App\Models\Project');
  }

  public function entries()
  {
    return $this->hasMany('App\Models\Entry');
  }
}
