<?php

declare(strict_types = 1);

namespace Laravelos\Traits;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

trait StructSetter
{
    public function __set($key, $value)
    {
        if (!property_exists($this, $key)) {
            return false;
        }

        if ($this->hasSetMutator($key)) {
            return $this->initMutatedValue($key, $value);
        }

        if ($customDataInfo = $this->getCustomDataTypeInfoByKey($key)) {
            return $this->initCustomDataType($key, $value, $customDataInfo);
        }
        $this->set($key, $value);
    }

    public function getCustomDataTypeInfoByKey($key)
    {
        $document = $this->getSelfReflectionPropertyDocComment($key);

        return $this->getCustomDataTypeInfoByDocument($document);
    }

    /**
     * 数组转对象通用方法
     *
     * @param array  $array         data
     * @param string $className     class name
     * @param object $classInstance class instance
     */
    public function arrayToObject(array $array, $className, $classInstance=null)
    {
        if (null === $classInstance) {
            $classInstance = new $className();
        }

        if ($classInstance instanceof self) {
            return $this->createClassByStruct($classInstance, $array);
        }

        return $this->createClassByReflection($classInstance, $array);
    }

    /**
     * Create objects with Struct
     *
     * @param array $array data
     *
     * @return Struct
     */
    public function createClassByStruct(self $classInstance, array $array)
    {
        foreach ($array as $key => $value) {
            $classInstance->__set($key, $value);
        }

        return $classInstance;
    }

    public function getMutatorKey($key)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
    }

    /**
     * Create objects with ReflectionClass
     *
     * @param Struct $classInstance
     * @param array  $array         data
     *
     * @return Struct
     */
    protected function createClassByReflection($classInstance, array $array)
    {
        $refClass = new ReflectionClass(get_class($classInstance));

        foreach ($refClass->getProperties() as $property) {
            if (isset($array[$property->getName()])) {
                $property->setAccessible(true);
                $value = $array[$property->getName()];

                if ($customDataInfo = $this->getCustomDataTypeInfoByDocument($property->getDocComment())) {
                    $value = $this->arrayToObject($value, $customDataInfo['class']);
                }
                $property->setValue($classInstance, $value);
            }
        }

        return $classInstance;
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function hasSetMutator($key)
    {
        return method_exists($this, 'set' . $this->getMutatorKey($key));
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param string $key
     */
    protected function initMutatedValue($key, $value)
    {
        return $this->{'set' . $this->getMutatorKey($key)}($value);
    }

    protected function getSuportCustomDataType()
    {
        return ['arrayObject'=>'array', 'object'=>'array', 'array'=>'array'];
    }

    protected function checkCustomType($key, $value, $customDataInfo)
    {
        $supportTypes    = $this->getSuportCustomDataType();
        $checkMethodName = 'checkCustomType' . ucfirst($supportTypes[$customDataInfo['type']]);

        if (method_exists($this, $checkMethodName)) {
            $this->$checkMethodName($key, $value, $customDataInfo);
        }
    }

    protected function checkCustomTypeArray($key, $value)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException("[$key] must is array");
        }
    }

    protected function set($key, $value): void
    {
        $this->$key = $value;
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param string $key
     */
    protected function initCustomDataType($key, $value, $customDataInfo)
    {
        $this->checkCustomType($key, $value, $customDataInfo);

        $initMethodName = 'init' . ucfirst($customDataInfo['type']);

        if (method_exists($this, $initMethodName)) {
            return $this->$initMethodName($key, $value, $customDataInfo);
        }

        return $this->set($key, $value);
    }

    protected function getSelfReflectionPropertyDocComment($key)
    {
        $propertyRef = new ReflectionProperty(get_class($this), $key);

        return $propertyRef->getDocComment();
    }

    /**
     * Initialize the value of the object type
     *
     * @param string $key            key
     * @param array  $value          value
     * @param array  $customDataInfo php document info
     */
    protected function initObject($key, array $value, array $customDataInfo)
    {
        $hasObject = isset($customDataInfo['class']) && ($class = $customDataInfo['class']);

        if (!$hasObject) {
            return $this->set($key, $value);
        }

        if (!class_exists($class)) {
            throw new InvalidArgumentException('class [' . $class . '] not exist');
        }

        return $this->set($key, $this->arrayToObject($value, $class));
    }

    /**
     * Initialize the value of the array type
     *
     * @param string $key            key
     * @param array  $value          value
     * @param array  $customDataInfo php document info
     */
    protected function initArray($key, array $value, array $customDataInfo)
    {
        $hasValue = isset($customDataInfo['column']) && ($column = $customDataInfo['column']);

        if (!$hasValue) {
            return $this->set($key, $value);
        }
        $setValue = [];

        foreach (explode(',', $customDataInfo['column']) as $column) {
            if (isset($value[$column])) {
                $setValue[$column] = $value[$column];
            }
        }

        return $this->set($key, $setValue);
    }

    /**
     * Initialize the value of the array object type
     *
     * @param string $key            key
     * @param array  $values         value
     * @param array  $customDataInfo php document info
     */
    protected function initArrayObject($key, array $values, array $customDataInfo)
    {
        $hasObject = isset($customDataInfo['class']) && ($class = $customDataInfo['class']);

        if (!$hasObject) {
            return $this->set($key, $values);
        }

        if (!isset($values[0])) {
            $values = [$values];
        }
        $objects = [];

        foreach ($values as $value) {
            $objects[] = $this->arrayToObject($value, $class);
        }

        return $this->set($key, $objects);
    }

    protected function getCustomDataTypeInfoByDocument($document): array
    {
        $docInfo      = $this->parseDocComment($document);
        $supportTypes = $this->getSuportCustomDataType();

        if (!isset($docInfo['type'], $supportTypes[$docInfo['type']])) {
            return [];
        }

        return $docInfo;
    }

    /**
     * Parse phpdocment
     *
     * @param string $doc doc
     *
     * @return array
     */
    protected function parseDocComment($doc)
    {
        $doc             = str_replace("\r\n", "\n", $doc);
        $docLines        = explode("\n", $doc);
        $docInfo         = [];

        foreach ($docLines as $line) {
            $lineArray = preg_split('/[\s@]+/', $line, -1, \PREG_SPLIT_NO_EMPTY);

            if (count($lineArray) == 3) {
                $docInfo[$lineArray[1]] = $lineArray[2];
            }
        }

        return $docInfo;
    }

    /**
     * @return Exception
     */
    protected function optimizerPlusLoadComments()
    {
        throw new Exception(
            'You have to enable opcache.load_comments=1 or zend_optimizerplus.load_comments=1.'
        );
    }
}
