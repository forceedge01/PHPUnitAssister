<?php

namespace PHPUnitAssister\Src\Core;


class invoker implements \PHPUnit_Framework_MockObject_Invocation {}

abstract class Mocker extends AssertionAssister {
    
    public $previousMock;
    public $mockObject;
    public $mockObjects = array();
    private $mockProviders = array();

    /******************************************* WRAPPER *******************************************/

    /**
     *
     * @param type $object
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     * @depreciated
     */
    public function setMock($object)
    {
        Debugger::TombStone('14-11-14');

        $this->setmo($object);
        
        return $this;
    }

    /**
     *
     * @param type $method
     * @param type $returnValue
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     */
    public function mock($method, $returnValue = null)
    {
        Debugger::TombStone('14-11-14');

        $this->mm($method, array('will' => $this->returnValue($returnValue)));

        return $this;
    }

    public function mockMultiple(array $methods, array $options = array())
    {
        Debugger::TombStone('14-11-14');

        $this->mmx($methods, $options);
    }

    /**
     *
     * @param array $methods
     * @param array $options
     */
    public function mockMethods(array $methods, array $options = array())
    {
        $this->mmx($methods, $options);

        return $this;
    }

    public function thenMock($method, array $options = array())
    {
        $this->then($method, $options);

        return $this;
    }

    /**
     * Get the final mocked object
     */
    public function getMockedObject()
    {        
        return $this->getmo();
    }

    public function getMockedObjects()
    {
        return $this->getmos();
    }

    public function setBaseMock()
    {
        $this->setbm();

        return $this;
    }

    /******************************************* END WRAPPER *******************************************/
    
    /******************************* NEW STUFF ************************************/
    
    public function setbm()
    {
        $this->mockObjects[] = $this->mockObjects[0];
        
        return $this;
    }
    
    public function then($method, array $options = array())
    {
        return $this->setMockObject($this->previousMock)
                ->mm($method, $options);
    }

    /**
     *
     * @param type $object
     * @param type $method
     * @param array $options
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     *
     * @description will mock a method with all phpunit options available, expects, with, will
     */
    public function mm($method, array $options = array())
    {
        $this->mockObject = end($this->mockObjects);

        if(is_array($method) and count($method) > 0)
        {
            foreach($method as $met)
            {
                $this->mmSingle($met, $options);
            }
        }
        else
        {
            $this->mmSingle($method, $options);
        }

        return $this;
    }
    
    private function mmSingle($method, array $options = array())
    {
        if(! is_object($this->mockObject))
        {
            throw new \Exception('Unable to mock method \''.$method.'\', expected mock object from class '.  get_called_class() . ', got: '.print_r($this->mockObject, true));
        }

        if(! method_exists($this->mockObject, $method))
        {
           throw new \Exception("Method '{$method}' does not exist for mock object " . get_class($this->mockObject));
        }

        $expects = isset($options['expects']) ? $options['expects'] : $this->any();
        $with = isset($options['with']) ? $options['with'] : '';
        $withArgs = isset($options['withArgs']) ? $options['withArgs'] : '';
        $will = isset($options['will']) ? $options['will'] : $this->returnSelf();
            
        $mocked = $this->mockObject->expects($expects)
                ->method($method);        
        
        $this
            ->performWith($mocked, $with)
            ->performWithArgs($mocked, $withArgs)
            ->performWill($mocked, $will);        
        
        return $this;
    }

    private function performWithArgs($mocked, $withArgs = null)
    {
        if($withArgs)
        {
            if(! is_array($withArgs))
            {
                // throw exception
                throw new \Exception('withArgs requires either an array or null value as input,  '.gettype($withArgs));
            }
            
            call_user_method_array('with', $mocked, $withArgs);
        }

        return $this;
    }
    
    private function performWith($mocked, $with = null)
    {
        if($with)
        {
            if(is_array($with) and isset($with['withArgs']))
            {
                call_user_method_array('with', $mocked, $with['withArgs']);
            }
            else
            {
                $mocked->with($with);
            }
        }
        
        return $this;
    }
    
    private function performWill($mocked, $will = null)
    {
        if(is_object($will))
        {
            if(get_class($will) === 'PHPUnit_Framework_MockObject_Stub_Return')
                $this->previousMock = $will->invoke(new invoker());
            
            $mocked->will($will);
        }
        else
            throw new \Exception('Will clause expects an object, ' . gettype($will) . ' provided');
        
        return $this;
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getmo()
    {   
        return $this->mockObjects[0];
    }

    /**
     * @return Array
     */
    public function getmos()
    {
        return $this->mockObjects;
    }
    
    /**
     * 
     * @param type $mockedObject
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     */
    public function setmo($mockedObject)
    {
        // Set property to empty array
        unset($this->mockObjects);
        unset($this->mockObject);
        unset($this->previousMock);

        $this->mockObjects = [];        
        $this->setMockObject($mockedObject);
        
        return $this;
    }
    
    private function setMockObject($mockedObject)
    {
        // Add mock to array
        $this->mockObjects[] = $mockedObject;
        
        return $this;
    }

    /**
     *
     * @param type $mockProviderClass
     * @return \PHPUnitAssister\Src\Extended\MockProvider
     */
    public function getMockProvider($mockProviderClass = 'MockProvider')
    {
        if(isset($this->mockProviders[$mockProviderClass]))
        {
            return $this->mockProviders[$mockProviderClass];
        }

        $qualifiedClass = "\\PHPUnitAssister\\Src\\Extensions\\$mockProviderClass";
        \PHPUnitAssister\Loader::LoadExtendedFileByClass($mockProviderClass);
        $this->mockProviders[$mockProviderClass] = new $qualifiedClass;

        return $this->mockProviders[$mockProviderClass];
    }

    /**
     *
     * @param array $methods
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     * @depreciated
     */
    public function mmx(array $methods, array $options = array())
    {
        if($options)
        {
            foreach($methods as $method)
            {
                $this->mm($method, $options);
            }
        }
        else
        {
            foreach($methods as $method => $value)
            {
                // Checks if the method variable contains an index or not
                if(is_int($method))
                {
                    $this->mm($value);
                }
                else
                {
                    $this->mm($method, array('will' => $this->returnValue($value)));
                }
            }
        }

        return $this;
    }
    
    /*******************************************************************/
}