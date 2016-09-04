<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends ApiModel
{
  use softDeletes;

  /**
   * The attributes that should be mutated to dates.
   *
   * @var array
   */
  protected $dates = ['date'];

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'entries';

  protected $fillable = ['title', 'price', 'date', 'content', 'project_id', 'category_id'];

  protected $rulesForCreation = [
    'title' => ['required'],
    'price' => ['required', 'integer', 'min:0'],
    'date' => ['required', 'date'],
    'project_id' => ['required', 'exists:projects,id'],
    'category_id' => ['required', 'exists:categories,id']
  ];

  protected $rulesForUpdate = [
    'title' => ['required'],
    'price' => ['required', 'integer', 'min:0'],
    'date' => ['required', 'date'],
    'project_id' => ['required', 'exists:projects,id'],
    'category_id' => ['required', 'exists:categories,id']
  ];

  public function project() {
    return $this->belongsTo('App\Models\Project');
  }

  public function category() {
    return $this->belongsTo('App\Models\Category');
  }

  public function setDateAttribute($value) {
    $this->attributes['date'] = Carbon::parse($value);
  }
}
