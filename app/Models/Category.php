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

  protected $fillable = ['title', 'color', 'by_default', 'project_id'];

  protected $rulesForCreation = [
    'title' => ['required', 'unique:categories,title,NULL,id,project_id,{project_id}'],
    'color' => ['required', 'hex_color'],
    'by_default' => ['boolean', 'one_default_category:{project_id}'],
    'project_id' => ['required', 'exists:projects,id'],
  ];

  protected $rulesForUpdate = [
    'title' => ['required', 'unique:categories,title,NULL,id,project_id,{project_id}'],
    'color' => ['required', 'hex_color'],
    'by_default' => ['boolean', 'one_default_category:{project_id},{id}']
  ];

  protected $errorMessages = [
    'required' => 'The :attribute field is required.',
    'one_default_category' => 'Only one default category per project',
    'unique' => 'The :attribute must be unique',
    'exists' => 'The :attribute must exist in the database',
    'boolean' => 'The :attribute must be a boolean',
    'hex_color' => 'The :attribute must be in the hexadecimal format'
  ];

  public function project() {
    return $this->belongsTo('App\Models\Project');
  }

  public function entries() {
    return $this->hasMany('App\Models\Entry')->orderBy('date', 'desc');
  }
}
