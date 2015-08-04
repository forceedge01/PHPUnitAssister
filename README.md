# README #

This README would normally document whatever steps are necessary to get your application up and running.

### What is this repository for? ###

* Quick summary
PHPUnit assister's main focus is to minimize the amount of duplicate code written for testing. Inital version focuses on mocking and asserting methods.

### How do I get set up? ###

### Install/Cloning the repo ###

With composer

```
{
	"require": {
		"genesis/phpunitassister": "1.0.*"
	}
}
```

* PHPUnit assister is a simple class library which is to be placed as a parent class of the actual test class.

* Configuration
There isnt much to configure, the mockProvider.php is a custom file where you can define your applications mock classes. Currently it works for a symfony2 app.
* Dependencies
PHPUnit
* Database configuration
No Database calls.
* How to run tests
Simply extend your test class with the objectHandler class and run the app, it should work as is even if written with plain PHPUnit methods. Once its working, use the methods provided.

```
// YourProject/Src/Bundle/Test/YourTestClass.Test.php

use PHPUnitAssister\Core\TestObjectHandler;

class YourTestClass extends TestObjectHandler {

    public function testYourMethod()
    {
        ...
    }
}
```

You can also choose to extend the assister with your own custom methods, a base symfony2 mock provider is already provided as an extended class.

```
// PHPUnitAssister/Src/Extensions/Symfony2MockProvider.Class.php

namespace PHPUnitAssister\Extensions;


use PHPUnitAssister\Core\TestObjectHandler;

/*
 * This class provides standard symfony2.3 mock objects
 */
class Symfony2MockProvider extends Mocker{
	...
}
```

```
// YourClass/ExtendedMockProvider.Class.php

class ExtendedMockProvider extends Mocker{ // or Symfony2MockProvider

}
```

### Contribution guidelines ###

* Feel free to contribute to this small project.

### Who do I talk to? ###

* Repo owner or admin

* phpunitAssister abbreviated calls
```
// YourProject/Src/Bundle/Test/YourTestClass.Test.php

// Setting the test object
$this->setTestObject($yourClassToTest, array $arguments);

// Mocking
$this->setmo(object $mocked) // set mock object
	->mm($method, array $args ...) // mock method
	->mmx(array $methods, array $options) // Mock multiple methods at once, stops chaining
	->then($method, array $args ...) // Chain next mock object with previous will clause object
	->getmo() // get mock object
	->getmos() // get mock objects
	->setbm() // Set base mock

// Assertions
$this->tm($method) // test method
	->with($param1, $param2) // params for the test method
	
	// Basic assertions
	->assert('true') // Assert that result is true
	->assert('true', 'your result') // Assert that result equals the string 'your result'
	->assert('false')
	->assert('equals', 'something-that-equals-result') // Perform equal assertion
	->assert('empty') // assert if result is empty
	->assert('notempty') // assert if result in non-empty
	
	// String position
	->assert('contains', 'This-would-be-something-you-expect-the-result-to-contain')
	->assert('true', '!==something-not-equal-to-result') // can be used with true or false
	
	// Regular expression assert
	->assert('regexp', '/some-regular-expression/i')
	
	// Array assertions
	->assert('isarray') // Assert that result is an array
	->assert('isarray', '[]==5') // Assert that array has a count of 5, can be used with true or false
	->assert('isarray', '[3]==example') // Assert that array index 3 is equal to example, can be used with true or false
	->assert('arrayhaskey', 'some-key-index')
	->setIndexToTest('someIndex') // Explicitly set the index to test
	
	// Object assertions
	->assert('isobject') // Assert that resultant is an object
	->assert('isobject', $classType) // Assert that the resultant object is of type $classType
	->setPropertyToTest('property') // Sets the property to be tested
	->assert('true', '->property==something') // Shortcut for the setPropertyToTest method, can be used with true or false
	->callMethodToTest('isLoggedIn') // Call method 'isLoggedIn' on resultant object and set its value as the test subject
	->assert('true') // Assert that the result of isLoggedIn is true
	
	// Generic calls
	->getTestResult(); // Returns the test result at any point

// Repeat call - can be used with specific assertion calls only
	->assert('isarray', '[3]==example') // Assert that array index 3 is equal to example
	->repeat() // Will repeat isarray with no params
	->repeat('[1]==repeated') // will repeat assert isarray with index 1 equal to repeated
	->repeat('[]==10') // will repeat assert is array with param array count equal to 10

// Resetting the initial result back to test
	->resetResultToTest();
```

* Examples

Setting your test class
```
// YourProject/Src/Bundle/Test/YourTestClass.Test.php

$this->SymfMockProvider = $this->getMockProvider('Symfony2MockProvider');

$this->setTestObject('Bundles\CampaignsBundle\Service\CampaignsService', array(
            'entityManager' => $this->SymfMockProvider->getEntityManagerMock(),
            'router' => $this->SymfMockProvider->getRouterMock(),
            'translator' => $this->SymfMockProvider->getTranslatorMock()
        ));
```

Altering your test class's dependency
```
$entityManager = $this->SymfMockProvider->getEntityManagerMock();

$this->resetTestObjectArgument('entityManager', $entityManager);
```

A cleaner and more contained way to mock (chain-mock)

PHPUnit standard
```
// YourProject/Src/Bundle/Test/YourTestClass.Test.php

// User mock to be injected in assertRepoMock
$userMock = $this->getUserMock();
$userMock->expects($this->any())
	->method('getEmail')
	->will($this->returnValue('example@phpunitAssister.com'));

// assertRepoMock to be injected in entityManager
$assertRepoMock = $this->SymfMockProvider->getRepositoryMock('AssetBundle:Image');
$assertRepoMock->expects($this->exactly(1))
        ->method('find')
        ->with(12)
        ->will($this->returnValue($userMock));

// Final object that contains the mocked objects
$entityManager = $this->SymfMockProvider->getEntityManagerMock();
$entityManager->expects($this->exactly(2))
        ->method('getRepository')
        ->with('AssetBundle:Image')
        ->will($this->returnValue($assertRepoMock));
```

PHPUnit assister - chain mocking the entity manager in symfony2
```
// YourProject/Src/Bundle/Test/YourTestClass.Test.php

// Starts at the entityManager
$entityManager = $this->setmo($this->SymfMockProvider->getEntityManagerMock())
	->mm('getRepository', [
		'expects' => $this->exactly(2),
		'with' => 'AssetBundle:Image',
		// Inject the mock directly, doesnt need to stored in another variable
		'will' => $this->returnValue($this->SymfMockProvider->getRepositoryMock('AssetBundle:Image'))
	])
	// Use the then call to further mock methods of the previously set object in the will clause
	->then('find', [
		'expects' => $this->exactly(1),
		'with' => 12,
		'will' => $this->returnValue($this->getUserMock())
	])
	// This will mock a method on the $this->getUserMock() resultant object
	->then('getEmail', [
		//Omit expects as its default value is $this->any()
		'will' => $this->returnValue('example@phpunitAssister.com')
	])
	// Finally get the mocked object to inject into the test object
	->getmo();
```

To use the userMock and AssetRepoMock from the previous example you can do
```
list($entityManager, $assetRepoMock, $userMock) = $this->setmo($this->SymfMockProvider->getEntityManagerMock())
	->mm('getRepository', [
		'expects' => $this->exactly(2),
		'with' => 'AssetBundle:Image',
		// Inject the mock directly, doesnt need to stored in another variable
		'will' => $this->returnValue($this->SymfMockProvider->getRepositoryMock('AssetBundle:Image'))
	])
	// Use the then call to further mock methods of the previously set object in the will clause
	->then('find', [
		'expects' => $this->exactly(1),
		'with' => 12,
		'will' => $this->returnValue($this->getUserMock())
	])
	// This will mock a method on the $this->getUserMock() resultant object
	->then('getEmail', [
		//Omit expects as its default value is $this->any()
		'will' => $this->returnValue('example@phpunitAssister.com')
	])
	// Getmos call gives you call mocks in sequential order
	->getmos();
```

Calling test methods and chained Assertions
```
// YourProject/Src/Bundle/Test/YourTestClass.Test.php

... // Mocking

// Set method to test
$this->tm('exampleMethod')
	// Specify params of method if any
	->with($param1, $param2)
	// assert if its an object, can also provide a specific object to test against
	->assert('isobject', $optionalSpecificObject)
	// call a method on the resultant object to further test its values
	->callMethodToTest('isLoggedIn')
	// check if the result of the isLoggedIn call is true
	->assert('true');
```

Calling a private/protected method to test
```
$this->setTestObjectMethodAccessible() // or $this->setma()
	->tm('privateMethod')->with()
	->assert...
```

The `->setTestObjectMethodAccessible` will set the next tested method using the `->tm` call to public accessibility. Must be called before the `->with` call in order to work.

To get the mock provider you can use `$this->provider` which essentially is the same as `$this->getMockProvider()`. Note that this will provide you with basic mocking capabilities. To create a new mock object you can use

```
// Args are optional in this case, this will provide a new mock with the constructor disabled.
$this->provider->getNewMock('\Your\Class', $args);
```

Creating extensions
-------------------

To create an extension place your extension mock provider in the extensions folder (Src/Extensions) with the namespace PHPUnitAssister\Extensions, the filenaming convention is `nameOfYourExtension.Class.php`

Once you have placed it in extensions folder, you can access your extension methods using the getMockProvider call

```
// YourProject/Src/Bundle/Test/YourTestClass.Test.php
... // Any code

$this->getMockProvider('nameOfYourExtension');
```