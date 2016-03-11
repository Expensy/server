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

  protected $fillable = ['title', 'color'];

  protected $commonRules = [
      'title' => ['required'],
      'color' => ['required']
  ];

  protected $rulesForCreation = [];
  protected $rulesForUpdate = [];

  public function project()
  {
    return $this->belongsTo('App\Models\Project');
  }
}
