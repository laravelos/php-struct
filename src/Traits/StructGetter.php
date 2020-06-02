<?php

declare(strict_types = 1);

namespace Laravelos\Traits;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

trait StructGetter
{
    public function __get($key)
    {
        if (!property_exists($this, $key)) {
            return null;
        }

        return $this->$key;
    }
}
