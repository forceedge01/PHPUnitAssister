<?php

namespace PHPUnitAssister\Src\Extended;


use PHPUnitAssister\Src\Core\Mocker;

class MockProvider extends Mocker{

    public function __construct() {
        // So that parent constructor isnt fired
    }
    
    public function getTwigEnvironmentMock()
    {
        $twig_Environment = $this->getMockBuilder('\Twig_Environment')
                ->getMock();

        return $twig_Environment;
    }

    public function getSampleObject(array $properties = array())
    {
        $stdObject = new \stdClass();

        foreach($properties as $property => $value)
        {
            $stdObject->$property = $value;
        }

        return $stdObject;
    }

    public function getNewSampleImage()
    {
        $imageString = 'iVBORw0KGgoAAAANSUhEUgAAABwAAAASCAMAAAB/2U7WAAAABl' .
            'BMVEUAAAD///+l2Z/dAAAASUlEQVR4XqWQUQoAIAxC2/0vXZDr' .
            'EX4IJTRkb7lobNUStXsB0jIXIAMSsQnWlsV+wULF4Avk9fLq2r' .
            '8a5HSE35Q3eO2XP1A1wQkZSgETvDtKdQAAAABJRU5ErkJggg==';

        return base64_decode($imageString);
    }
}