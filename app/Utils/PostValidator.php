<?php


namespace App\Utils;


class PostValidator
{
    /**
     * @var boolean
     */
    public $passes;

    /**
     * @var array
     */
    public $messages;

    function __construct($passes, $messages)
    {
        $this->passes = $passes;
        $this->messages = $messages;
    }
}