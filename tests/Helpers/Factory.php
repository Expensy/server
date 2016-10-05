<?php
namespace Helpers;

trait Factory
{
  protected function times($int)
  {
    $this->times = $int;

    return $this;
  }

  protected function make($type, array $fields = [])
  {
    while ($this->times--) {
      $stub = array_merge($this->getStub(), $fields);
      $type::create($stub);
    }
  }

  protected function getStub()
  {
    throw new BadMethodCallException('Create your own `getStub()` method');
  }

}