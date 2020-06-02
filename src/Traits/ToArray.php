<?php

declare(strict_types = 1);

namespace Laravelos\Traits;

use ReflectionClass;

trait ToArray
{
    public function toArray()
    {
        $data         = get_object_vars($this);
        $supportTypes = ['arrayObject', 'object'];

        foreach ($data as $key=>$value) {
            $data[$key] = $this->valueToArray($value);
        }

        return $data;
    }

    protected function valueToArray($value)
    {
        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                return $value->toArray();
            }

            return $this->objectToArrayByReflection($value);
        }

        if (is_array($value)) {
            $objectValues = [];

            foreach ($value as $k=>$v) {
                $objectValues[$k] = $this->valueToArray($v);
            }

            return $objectValues;
        }

        return $value;
    }

    protected function objectToArrayByReflection($classInstance)
    {
        $array    = [];
        $refClass = new ReflectionClass(get_class($classInstance));

        foreach ($refClass->getProperties() as $property) {
            $property->setAccessible(true);
            $value                       = $property->getValue($classInstance);
            $array[$property->getName()] = $this->valueToArray($value);
        }

        return $array;
    }
}
