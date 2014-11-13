# README #

This README would normally document whatever steps are necessary to get your application up and running.

### What is this repository for? ###

* Quick summary
PHPUnit assister's main focus is to minimize the amount of duplicate code written for testing. Inital version focuses on mocking and asserting methods.
* Version 0.1

### How do I get set up? ###

### Install/Cloning the repo ###

With composer

```
{
	"require": {
		"genesis/phpunitassister": "0.1.*"
	}
}
```

```
git clone git@bitbucket.org:wqureshi/phpunitassister.git PHPUnitAssister
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
// PHPUnitAssister/Src/Core/TestObjectHandler.Class.php
<?php

use PHPUnitAssister\Src\Core\TestObjectHandler;

class YourTestClass extends TestObjectHandler {

    public function testYourMethod()
    {
        ...
    }
}
```

You may also need to set the namespace where the webTestCase class exists in your project for phpunit in the AssertionAssister file
```
// PHPUnitAssister/Src/Core/AssertionAssister.Class.php
<?php

namespace PHPUnitAssister\Src\Core;

// Change namespace according to your project settings
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AssertionAssister extends WebTestCase {

```

You can also choose to extend the assister with your own custom methods, a base symfony2 mock provider is already provided as an extended class.

```
// PHPUnitAssister/Src/Extensions/Symfony2MockProvider.Class.php
<?php

namespace PHPUnitAssister\src\Extensions;


use PHPUnitAssister\src\Core\TestObjectHandler;

/*
 * This class provides standard symfony2.3 mock objects
 */
class Symfony2MockProvider extends Mocker{
	...
}
```

```
// YourClass/ExtendedMockProvider.Class.php
<?php

class ExtendedMockProvider extends Mocker{ // or Symfony2MockProvider

}
```

### Contribution guidelines ###

* Feel free to contribute to this small project.

### Who do I talk to? ###

* Repo owner or admin

* Examples

Setting your test class
```
$this->SymfMockProvider = ->getMockProvider('Symfony2MockProvider');

$this->setTestObject('Bundles\CampaignsBundle\Service\CampaignsService', array(
            'entityManager' => $this->SymfMockProvider->getEntityManagerMock(),
            'router' => $this->SymfMockProvider->getRouterMock(),
            'translator' => $this->SymfMockProvider->getTranslatorMock()
        ));
```

Altering your test class's dependecy
```
$entityManager = $this->SymfMockProvider->getEntityManagerMock();

$this->resetTestObjectArgument('entityManager', $entityManager);
```

A cleaner and more contained way to mock (chain-mock)

PHPUnit standard
```
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

PHPUnit assister
```
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

Calling test methods and chained Assertions
```
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