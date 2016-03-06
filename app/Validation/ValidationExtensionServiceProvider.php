<?php

namespace App\Validation;

use Illuminate\Support\ServiceProvider;

class ValidationExtensionServiceProvider extends ServiceProvider
{

    public function register()
    {
        // TODO: Implement register() method.
    }

    public function boot()
    {
        $this->app->validator->resolver(function ($translator, $data, $rules, $messages = [], $customAttributes = []) {
            return new OldPasswordValidator($translator, $data, $rules, $messages, $customAttributes);
        });
    }
}