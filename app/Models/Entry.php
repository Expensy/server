<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends ApiModel
{
  use softDeletes;

  protected $dates = ['date'];

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'entries';

  protected $fillable = ['title', 'price', 'date', 'content'];

  protected $commonRules = [
      'title'       => ['required'],
      'price'       => ['required', 'integer', 'min:0'],
      'date'        => ['required', 'date'],
      'category_id' => ['required', 'exists:categories,id']
  ];

  protected $rulesForCreation = [];
  protected $rulesForUpdate = [];

  public function project()
  {
    return $this->belongsTo('App\Models\Project');
  }

  public function category()
  {
    return $this->belongsTo('App\Models\Category');
  }
}
