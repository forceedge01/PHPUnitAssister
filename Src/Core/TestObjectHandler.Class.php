<?php

namespace PHPUnitAssister\Src\Core;


abstract class TestObjectHandler extends Mocker{

    protected $testObject;
    protected $args;

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

    public function setPropertyValue($property, $value)
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

    public function setMessage($expected, $response)
    {
        $formattedResponse = $response;

        if(is_object($formattedResponse))
        {
            $formattedResponse = 'instance of '.get_class($formattedResponse);
        }
        else if(is_array($formattedResponse))
        {
            $formattedResponse = print_r($formattedResponse, true);
        }

        $formattedExpected = $expected;

        if(is_object($formattedExpected) || is_array($formattedExpected))
        {
            $formattedExpected = print_r($formattedExpected, true);
        }

        return "\n\nExpected (++) \nActual (--) \n\n@++ $formattedExpected\n@-- $formattedResponse\n";
    }

    public function setReflection($class)
    {
        $this->reflection = new \ReflectionClass($class);

        return $this;
    }
    
    public function getTestObject()
    {
        return $this->testObject;
    }
}