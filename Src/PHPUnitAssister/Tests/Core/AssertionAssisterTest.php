<?php

namespace PHPUnitAssister\Tests\Core;

class example {

	private $property;
	public $publicProperty = 5;

	public function xyz() {
		return $property;
	}
}

/**
 * @coversDefaultClass PHPUnitAssister\Src\Core\AssertionAssister;
 */
class AssertionAssisterTests extends \PHPUnit_Framework_TestCase {
	
	private $testObject, $reflection;

	public function __construct() 
	{
		// Set reflection to access private/protected properties
		$this->reflection = new \ReflectionClass('\PHPUnitAssister\Core\AssertionAssister');

		// The actual object on which tests are to be performed
		$this->testObject = $this->getMockForAbstractClass('\PHPUnitAssister\Core\AssertionAssister');
	}

	/**
	 * @covers ::setPropertyToTest
	 */
	public function testsetPropertyToTest() 
	{
		// Set property accessible of the abstract class
		$property2 = $this->reflection->getProperty( 'totest' );
		$property2->setAccessible( true );
		// Assign the example class as the class that is being tested within this method
		$property2->setValue($this->testObject, new \PHPUnitAssister\Tests\Core\example());

		// Invoke method on abstract class
		$result = $this->testObject->setPropertyToTest('publicProperty');

		// Access value of abstract class mock to see if value is correct
		$this->assertEquals($property2->getValue($this->testObject), 5);
	}
}