<?php

namespace PHPUnitAssister\Core;

abstract class TestObjectHandler extends Mocker{

    protected $testObject;
    protected $args;
    protected $provider;


    public function __construct()
    {
        $this->provider = $this->getMockProvider();
    }

    /**
     *
     * @param type $class
     * @param array $args
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     */
    public function setTestObject($class, array $args = array())
    {
        $this->setReflection($class);
        $this->args = $args;

        if($this->args)
        {
            $this->testObject = $this->reflection->newInstanceArgs($this->args);
        }
        else
        {
            $this->testObject = $this->reflection->newInstance();
        }

        return $this;
    }

    public function setTestObjectProperty($property, $value)
    {
        $this->setReflection($this->testObject);
        $reflectionProperty = $this->reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->testObject, $value);

        return $this;
    }

    /**
     *
     * @param array $args
     * @return \PHPUnitAssister\Src\Core\TestObjectHandler
     */
    public function resetTestObject(array $args)
    {
        $this->setReflection($this->testObject);
        $this->args = $args;
        $this->testObject = $this->reflection->newInstanceArgs($this->args);

        return $this;
    }

    /**
     *
     * @param type $index
     * @param type $argument
     * @return \PHPUnitAssister\Src\Core\TestObjectHandler
     */
    public function resetTestObjectArgument($index, $argument)
    {
        $this->args[$index] = null;
        $this->args[$index] = $argument;
        $this->resetTestObject($this->args);

        return $this;
    }

    /**
     *
     * @param type $method
     */
    public function setTestObjectMethodAccessible()
    {
        $this->setMethodAccessible = 1;

        return $this;
    }

    public function setma()
    {
        return $this->setTestObjectMethodAccessible();
    }
    
    public function getTestObject()
    {
        return $this->testObject;
    }

    private function setReflection($class)
    {
        $this->reflection = new \ReflectionClass($class);

        return $this;
    }
}