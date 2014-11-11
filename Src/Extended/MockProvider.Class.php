<?php

namespace PHPUnitAssister\Src\Extended;


use PHPUnitAssister\Src\Core\Mocker;

class MockProvider extends Mocker{

    public function __construct() {
        // So that parent constructor isnt fired
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

    public function getCurlMock()
    {
        $serviceMock = $this->getMockBuilder('\Bundles\CoreBundle\Service\CurlService', array());
        $serviceMock->disableOriginalConstructor();

        return $serviceMock->getMock();
    }

    public function getSoapMock()
    {
        $serviceMock = $this->getMockBuilder('\Soapclient', array(
            'WSDL_URL' => "http://ws.webgains.com/aws.php"
        ));

        $serviceMock->disableOriginalConstructor();

        return $serviceMock->getMock();
    }
}