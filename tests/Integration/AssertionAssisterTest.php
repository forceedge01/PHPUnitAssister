<?php

namespace PHPUnitAssister\Tests\Integration;

class example
{
    private $property;
    public $publicProperty = 5;

    public function xyz()
    {
        return $property;
    }
}

class AssertionAssisterTests extends \PHPUnit_Framework_TestCase
{
    private $testObject;

    private $reflection;

    public function __construct()
    {
        // Set reflection to access private/protected properties
        $this->reflection = new \ReflectionClass('\PHPUnitAssister\Core\AssertionAssister');

        // The actual object on which tests are to be performed
        $this->testObject = $this->getMockForAbstractClass('\PHPUnitAssister\Core\AssertionAssister');
    }

    public function testsetPropertyToTest()
    {
        // Set property accessible of the abstract class
        $property2 = $this->reflection->getProperty('totest');
        $property2->setAccessible(true);
        // Assign the example class as the class that is being tested within this method
        $property2->setValue($this->testObject, new example());

        // Invoke method on abstract class
        $result = $this->testObject->setPropertyToTest('publicProperty');

        // Access value of abstract class mock to see if value is correct
        $this->assertEquals($property2->getValue($this->testObject), 5);
    }
}
