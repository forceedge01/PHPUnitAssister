<?php

namespace PHPUnitAssister\src\Core;

// Change namespace according to your project settings
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AssertionAssister extends WebTestCase {
    
    protected $totest, $result, $lastMethod = [], $method, $reflectionMethod, $reflection;
    
    /**
     * @deprecated - use tm and with instead
     * 
     * @param type $method
     * @param type $params
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     */
    public function method($method, $params = false)
    {
        $this->reflectionMethod = $this->reflection->getMethod($method);
        
        if($params === false)
            $this->totest = $this->result = $this->reflectionMethod->invoke($this->testObject);
        else
            $this->totest = $this->result = $this->reflectionMethod->invokeArgs($this->testObject, $params);
        
        return $this;
    }
    
    public function getResult()
    {
        return $this->result;
    }
    
    public function getTestResult()
    {
        return $this->totest;
    }

    public function assertWith($params, $type, $expected = null)
    {
        if($params === false)
            $this->method($this->reflectionMethod->getName())
                ->assert($type, $expected);
        else
            $this->method($this->reflectionMethod->getName(), $params)
                ->assert($type, $expected);

        return $this;
    }
    
    public function setPropertyToTest($property)
    {
        $this->lastMethod[] = __METHOD__;
        
        if(! isset($this->totest->$property))
            $this->throwException (new \Exception("Expected property: $property in ".  get_class($this->totest)));
        
        $this->totest = $this->totest->$property;
        
        return $this;
    }
    
    public function callMethodToTest($method, $args = array())
    {
        $this->lastMethod[] = __METHOD__;
        
        if(! method_exists($this->totest, $method))
            throw new \Exception("object method '{$method}' not found in ".  get_class($this->totest));

        if(! is_object($this->totest))
            throw new \Exception("Cannot call method on a non object");
        
        $this->totest = call_user_method_array($method, $this->totest, $args);
        
        return $this;
    }
    
    public function setTestResult($testable)
    {
        $this->lastMethod[] = __METHOD__;
        
        $this->totest = $testable;
        
        return $this;
    }
    
    /**
     * Accepts new params
     */
    public function repeat()
    {   
        $args = func_get_args();        
        $method = end($this->lastMethod);
        
        if(empty($method))
            $this->throwException (new \Exception("cannot repeat empty method given in ".  get_class($this->totest)));
        
        call_user_method_array($method, $this, $args);
        
        return $this;
    }
    
    public function setIndexToTest($index)
    {
        $this->lastMethod[] = __METHOD__;
        
        if(! isset($this->totest[$index]))
            $this->throwException (new \Exception("Expected index: $index in ".  print_r($this->totest, true)));
        
        $this->totest = $this->totest[$index];
        
        return $this;
    }
    
    public function resetResultToTest()
    {
        $this->totest = $this->result;
        
        return $this;
    }
    
    private function isAssertionFunc($type, $expected = null)
    {
        $asserted = false;
        
        switch (strtolower($type))
        {
            case 'regexp':
            {
                $this->assertRegExp($expected, $this->totest, $this->setMessage('match regex '.$expected, $this->totest));
                $asserted = true;
                break;
            }
            case 'arrayhaskey':
            {
                $this->assertArrayHasKey($expected, $this->totest, $this->setMessage('array has key '.$expected, $this->totest));
                $asserted = true;
                break;
            }
            case 'isarray':
            {
                $this->assertTrue(is_array($this->totest), $this->setMessage('should be an array', $this->totest));
                $asserted = true;
                break;
            }
            case 'isobject':
            {
                if($expected)
                {
                    if(is_object($expected))
                        $expected = get_class ($expected);
                    
                    $this->assertTrue(get_class($this->totest) == trim($expected,'\\'), $this->setMessage("should be an object of class type '$expected'", $this->totest));    
                }
                else
                    $this->assertTrue(is_object($this->totest), $this->setMessage('should be an object', $this->totest));
                
                $asserted = true;
                break;
            }
            case 'contains':
            {
                $this->assertTrue((strpos($this->totest, $expected) !== false), $this->setMessage("expected '$expected' to be in result", $this->totest));
                
                $asserted = true;
                break;
            }
        }
        
        if($asserted)
            return true;
        
        return false;
    }
    
    private function resolveStringBasedAssertion($type, $expected = null)
    {
        $assertMethod = $this->getAssertMethod($type);
        
        // Assert type of result if expected is an object
        if(is_object($expected))
        {
            $this->$assertMethod($this->totest instanceof $expected, $this->setMessage('instance of '.  get_class($expected), $this->totest));
        }
        // If there is the special character in the string, do these extra bits
        else if(! is_array($expected) and strpos($expected, '=='))
        {
            list($assertType, $val) = explode('==', $expected);

            switch($assertType)
            {
                // Add more types in here if needed
                case '!':
                    $this->$assertMethod($this->totest !== $val, $this->setMessage('not to be '.$val, $this->totest));
                    break;
                case '[]':
                    $this->$assertMethod(count($this->totest) == $val, $this->setMessage('array elements count be equal to '.$val, count($this->totest)));
                    break;
                case '->':
                    $this->$assertMethod($this->totest instanceof $val, $this->setMessage('object instance of '.$val, $this->totest));
                    break;
                default:
                    $this->resolveUnusualAssertion($assertMethod, $assertType, $val);
                    break;
            }
        }
        // Execute normal assert with expected result
        else
        {
            if($expected == 'null')
                $this->$assertMethod($this->totest == null, $this->setMessage($expected, $this->totest));
            else
                $this->$assertMethod($this->totest == $expected, $this->setMessage($expected, $this->totest));
        }
    }
    
    private function resolveUnusualAssertion($assertMethod, $type, $val)
    {
        if(preg_match("/^\[.+\]$/", $type))
        {
            $index = trim(trim($type, '['), ']');

            if($val == 'null')
                $this->$assertMethod($this->totest[$index] == null, $this->setMessage("Index '$index' not equal to null in ".print_r($this->totest, true), $this->totest));
            else
                $this->$assertMethod($this->totest[$index] == $val, $this->setMessage("Index '$index' not equal to '$val' in ".print_r($this->totest, true), $this->totest));
        }
        else if(preg_match("/^->.+$/", $type))
        {
            $property = str_replace('->', '', $type);

            if($val == 'null')
                $this->$assertMethod($this->totest->$property == null, $this->setMessage("Property '$property' not equal to null in ".get_class($this->totest), $this->totest));
            else
                $this->$assertMethod($this->totest->$property == $val, $this->setMessage("Property '$property' not equal to '$val' in ".get_class($this->totest), $this->totest));
        }
    }
    
    private function getAssertMethod($type)
    {
        $methodType = ucfirst($type);
        
        return "assert{$methodType}";
    }
    
    public function assert($type, $expected = null)
    {
        $assertMethod = $this->getAssertMethod($type);        
        $asserted = $this->isAssertionFunc($type, $expected);

        if(! $asserted)
        {
            if($expected || is_array($expected))
            {
                $this->resolveStringBasedAssertion($type, $expected);
            }
            // Normal assertion
            else
            {
                $this->$assertMethod($this->totest, $this->setMessage($type, $this->totest));
            }
        }

        return $this;
    }

    public function assertSelfInstance($result)
    {
        $obj = $this->getTestObject();

        $this->assertTrue($result instanceof $obj, $this->setMessage('instance of '.  get_class($obj), $result));

        return $this;
    }
    
    /*************************************************** NEW STUFF **********************************************/
    
    /**
     * 
     * @param type $method
     * @return \Bundles\CoreBundle\Tests\Service\ExtendedTestCase
     */
    public function tm($method)
    {
        $this->method = $method;
        
        return $this;
    }
    
    /**
     * 
     * @return type
     */
    public function with()
    {
        $args = func_get_args();
        
        $this->method($this->method, $args);
        
        return $this;
    }
    
    public function setExpectedExc($exception)
    {
        $this->setExpectedException($exception);
        
        return $this;
    }
    
    /************************************************** END OF NEW STUFF ***************************************************/

    public function prex()
    {
        $arguments = func_get_args();

        foreach($arguments as $argument)
        {
            print_r($argument);
        }

        exit();
    }
}