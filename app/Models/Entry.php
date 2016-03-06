<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends ApiModel
{
  use softDeletes;

  protected $dates = ['deleted_at'];

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'entries';

  protected $fillable = ['title', 'price', 'date', 'content', 'user_id', 'category_id'];

  protected $notifiable = ['title', 'price', 'date', 'content'];

  protected $commonRules = [
      'title' => 'required',
      'price' => ['required', 'integer', 'min:0'],
      'date'  => ['required', 'date']
  ];

  protected $rulesForCreation = [];
  protected $rulesForUpdate = [];

  public function user()
  {
    return $this->belongsTo('User');
  }

  public function category()
  {
    return $this->belongsTo('Category');
  }
}
