<?php

namespace PHPUnitAssister\Src\Core;


class invoker implements \PHPUnit_Framework_MockObject_Invocation {}

abstract class Mocker extends AssertionAssister {
    
    public $previousMock;
    private $mockObject;
    public $mockObjects = array();
    
     /**
     *
     * @param type $mockedObject
     * @param type $method
     * @param type $returnValue
     */
    public function mockMethod($mockedObject, $method, $returnValue = null)
    {
        $mockedObject->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));

        return $mockedObject;
    }

    /**
     *
     * @param type $object
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     * @depreciated
     */
    public function setMock($object)
    {
        $this->setmo($object);
        
        return $this;
    }

    /**
     *
     * @param type $method
     * @param type $returnValue
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     * @depreciated
     */
    public function mock($method, $returnValue = null)
    {
        $this->mm($method, array('will' => $this->returnValue($returnValue)));

        return $this;
    }

    /**
     *
     * @param array $methods
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     * @depreciated
     */
    public function mockMultiple(array $methods, array $options = array())
    {
        if($options)
        {
            foreach($methods as $method)
            {
                $this->mm($method, $options);
//                $this->mock($method, $singleReturn);
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
//                    $this->mock($value, null);
                }
                else
                {
                    $this->mm($method, array('will' => $this->returnValue($value)));
//                    $this->mock($method, $value);
                }
            }
        }

        return $this;
    }

    /**
     * 
     * @depreciated
     */
    public function getMockObject()
    {
        return $this->mockObject;
    }
    
    /******************************* NEW STUFF ************************************/
    
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
            $this->mmSingle($method, $options);
        
        return $this;
    }
    
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
    
    private function mmSingle($method, array $options = array())
    {
        $expects = isset($options['expects']) ? $options['expects'] : $this->any();
        $with = isset($options['with']) ? $options['with'] : '';
        $withArgs = isset($options['withArgs']) ? $options['withArgs'] : '';
        $will = isset($options['will']) ? $options['will'] : $this->returnSelf();
        
        if(! is_object($this->mockObject))
            throw new \Exception('Unable to mock method \''.$method.'\', expected mock object from class '.  get_called_class() . ', got: '.print_r($this->mockObject, true));
            
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
     * 
     * @param type $mockedObject
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     */
    public function setmo($mockedObject)
    {        
        $this->mockObjects = [];
        
        $this->setMockObject($mockedObject);
        
        return $this;
    }
    
    private function setMockObject($mockedObject)
    {
        $this->mockObjects[] = $mockedObject;
        
        return $this;
    }
    
    /*******************************************************************/
}