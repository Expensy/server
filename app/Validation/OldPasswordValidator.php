<?php

namespace App\Validation;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator as IlluminateValidator;

class OldPasswordValidator extends IlluminateValidator
{
    protected $myCustomMessages = [
        'old_password' => "The :attribute is not correct."
    ];

    function __construct($translator, $data, $rules, $messages, $customAttributes)
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);

        $this->setCustomInformation();
    }

    protected function setCustomInformation()
    {
        $this->setCustomMessages($this->myCustomMessages);
    }

    public function validateOldPassword($attribute, $value, $parameters)
    {
        return Hash::check($value, Auth::user()->getAuthPassword());
    }
}