<?php

namespace Helpers;


use App\Models\Enum\BasicEnum;

class AuthEnum extends BasicEnum
{
  const NONE = 0;
  const CORRECT = 1;
  const WRONG = 2;
}