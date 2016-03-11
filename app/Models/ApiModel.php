<?php


namespace App\Models;


use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enum\Action;
use Illuminate\Support\Facades\Validator;
use Underscore\Types\Arrays;

class ApiModel extends Model
{
  /**
   * common rules validation for creation and update
   *
   * final rules are a concatenation of $commonRules and $rulesForCreation/$rulesForUpdate
   * @var
   */
  protected $commonRules;

  /**
   * rules exclusively for creation
   * @var
   */
  protected $rulesForCreation;

  /**
   * rules exclusively for update
   * @var
   */
  protected $rulesForUpdate;

  protected $errors;

  /**
   * Validate data from validation rules
   *
   * @param $data   Object the data to validate
   * @param $action String the action (CREATION or UPDATE)
   *
   * @return bool true if validated, false otherwise
   */
  public function validate($data, $action)
  {
    $rules = [];
    switch ($action) {
      case Action::CREATION :
        $rules = $this->getRulesForCreation();
        break;
      case Action::UPDATE :
        $allRules = $this->getRulesForUpdate();

        $rules = Arrays::invoke($allRules, function ($rules) use ($data) {
          return Arrays::invoke($rules, function ($rule) use ($data) {
            if (strpos($rule, '{id}') !== false) {
              return str_replace('{id}', $data['id'], $rule);
            }

            return $rule;
          });
        });
        break;
    }

    $validator = Validator::make($data, $rules);

    if ($validator->fails()) {
      $this->errors = $validator->messages()->toArray();

      return false;
    }

    return true;
  }

  /**
   * Get the concatenation of commonRules and rulesForCreation
   *
   * @throws BadMethodCallException
   * @return mixed
   */
  public function getRulesForCreation()
  {
    if (is_null($this->commonRules)) {
      throw new BadMethodCallException('Add your `$commonRules` array');
    }
    if (is_null($this->rulesForCreation)) {
      throw new BadMethodCallException('Add your `$rulesForCreation` array');
    }

    return Arrays::merge($this->commonRules, $this->rulesForCreation);
  }

  /**
   * Get the concatenation of commonRules and rulesForUpdate
   *
   * @throws BadMethodCallException
   * @return mixed
   */
  public function getRulesForUpdate()
  {
    if (is_null($this->commonRules)) {
      throw new BadMethodCallException('Add your `$commonRules` array');
    }
    if (is_null($this->rulesForUpdate)) {
      throw new BadMethodCallException('Add your `$rulesForUpdate` array');
    }

    return Arrays::merge($this->commonRules, $this->rulesForUpdate);
  }

  /**
   * Get the array of error messages
   *
   * @return mixed
   */
  public function errors()
  {
    return $this->errors;
  }
}

