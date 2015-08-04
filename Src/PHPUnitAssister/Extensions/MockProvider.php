<?php

namespace PHPUnitAssister\Extensions;

use PHPUnitAssister\Core\Mocker;

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

    // Get new mock with disabled constructor
    public function getNewMock($class, array $args = array())
    {
        return $this->getMockBuilder($class, $args)
            ->disableOriginalConstructor()
            ->getMock();
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
        return $this->getNewMock('\Bundles\CoreBundle\Service\CurlService');
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