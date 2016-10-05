<?php
/**
 * Created by PhpStorm.
 * User: pierrebaron
 * Date: 06/03/2016
 * Time: 22:42
 */

namespace App\Models\Enum;

class AuthEnum extends BasicEnum
{
  const SUCCESS = 0;
  const FORBIDDEN = 1;
  const INTERNAL_ERROR = 2;
}
