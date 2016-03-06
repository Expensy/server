<?php


use App\Models\ApiModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends ApiModel
{
  use SoftDeletes;

  protected $dates = ['deleted_at'];
  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'categories';

  protected $fillable = ['title', 'color', 'user_id'];

  protected $notifiable = ['title', 'color'];

  protected $commonRules = [
      'title' => 'required',
      'color' => ['required']
  ];

  protected $rulesForCreation = [];
  protected $rulesForUpdate = [];

  public function user()
  {
    return $this->belongsTo('User');
  }
}
