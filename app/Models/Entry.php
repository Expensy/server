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
    'category_id' => ['required', 'exists:categories,id']
  ];

  protected $errorMessages = [
    'required' => 'The :attribute field is required.',
    'integer' => 'The :attribute must be an integer',
    'min' => 'The :attribute must have a minimum value of 0',
    'date' => 'The :attribute must be a date with the following format YYYY-MM-dd',
    'exists' => 'The :attribute must exist in the database'
  ];

  public function project() {
    return $this->belongsTo('App\Models\Project');
  }

  public function category() {
    return $this->belongsTo('App\Models\Category');
  }
}
