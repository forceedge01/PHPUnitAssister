<?php

namespace Bundles\CoreBundle\TestsHelperSuite;



abstract class MockProvider extends Mocker{
    
    // Default values for mocks
    public $campaignId = 0;
    public $accountId = 0;
    public $userId = 0;
    public $imageId = 0;
    public $endUser = 0;
    public $membership = 0;
    public $goalId = 0;
    public $rewardId = 0;
    public $affiliateNetworkId = 0;
    public $emailTypeId = 0;
    public $emailId = 0;
    private $mocks = [];
    
    /**
     *
     * @param type $service
     * @param array $options - array args = array(), mixed methods = array(), string name = null;
     * @return type
     *
     * This is the only method that should be used for mocking service objects
     */
    public function getServiceMock($service, array $options = array('args' => false))
    {        
        if(strpos($service, ':'))
        {
            list($bundle, $serviceName) = explode(':', $service);
            $service = "\\Bundles\\{$bundle}\\Service\\{$serviceName}";
        }
        
        $serviceMock = $this->getMockBuilder($service);
        
        if(! is_object($serviceMock))
            throw new \Exception("Unable to mock, Service '{$service}' not found.");

        if($options)
        {
            if( @$options['args'] == false)
                $serviceMock
                    ->disableOriginalConstructor();
            else if( @$options['args'] == 'all')
                $serviceMock
                    ->enableArgumentCloning();
            else
                $serviceMock
                    ->setConstructorArgs($options['args']);

            if( @$options['name'])
                $serviceMock
                    ->setMockClassName($options['name']);

            if( @$options['methods'])
                $serviceMock
                    ->setMethods($options['methods']);
        }

        return $serviceMock->getMock();
    }

    public function getUserMailerServiceMock($methods = array())
    {
        $mailer = $this->getMockBuilder('\Bundles\MailerBundle\Service\UserMailerService')
                ->setMethods($methods)
                ->disableOriginalConstructor()
                ->getMock();

        return $mailer;
    }
    
    public function getCoreMailerServiceMock($methods = array())
    {
        $mailer = $this->getMockBuilder('Bundles\MailerBundle\Service\CoreMailerService')
                ->setMethods($methods)
                ->disableOriginalConstructor()
                ->getMock();

        return $mailer;
    }

    public function getTwigEnvironmentMock()
    {
        $twig_Environment = $this->getMockBuilder('\Twig_Environment')
                ->getMock();

        return $twig_Environment;
    }

    public function getSessionMock($methods = array())
    {
        $sess = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
                ->setMethods($methods)
                ->getMock();

        return $sess;
    }
    
    

    public function getEntityManagerMock($entityColonBundle = null, $methods = array())
    {
        $entityManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                    ->setMethods($methods)
                    ->disableOriginalConstructor()
                    ->getMock();

        $this->mockMethod($entityManagerMock, 'persist');

        // So the entity manager has a real get repository function from the original entity
        if($entityColonBundle)
        {
            $entityMock = $this->getEntityMock($entityColonBundle, array(
                'getId' => 1
            ));

            $repoMock = $this->getRepositoryMock($entityColonBundle, array(), $entityMock);
            $this->mockMethod($entityManagerMock, 'getRepository', $repoMock);
        }

        return $entityManagerMock;
    }

    /**
     *
     * @param type $repository
     * @param array $magicMethods
     * @param type $returnValue
     * @return type
     */
    public function getRepositoryMock($entityColonBundle = null, array $methods = array(), $returnValue = null)
    {
        $method = null;

        if(! is_array($methods))
        {
            $method = $methods;
            $methods = array();
        }

        if($entityColonBundle)
        {
            if(strpos($entityColonBundle, ':'))
            {
                list($bundle, $repo) = explode(':', $entityColonBundle);
                $repository = "Bundles\\{$bundle}\Repository\\{$repo}Repository";
            }
            else
            {
                $repository = $entityColonBundle;
            }
            
            if(! class_exists($repository))
                throw new \Exception("Class '$repository' does not exist, unable to mock repo");

            $repo = $this->getServiceMock($repository, array('args' => false, 'methods' => $methods));
        }
        else
            $repo = $this->getServiceMock('\Doctrine\ORM\EntityRepository', array('args' => false, 'methods' => $methods));


        if($returnValue === 'entity')
        {
            $returnValue = $this->getEntityMock($entityColonBundle);
        }

        if($returnValue)
        {
            $this->setMock($repo)
                    ->mockMultiple(array(
                        'find' => $returnValue,
                        'findAll' => array($returnValue),
                        'findBy' => array($returnValue),
                        'findOneBy' => $returnValue
                    ));
        }


        return $repo;
    }
    
    public function getTemplateHelperMock()
    {
        $mock = $this->getServiceMock('\Symfony\Component\Templating\Helper\CoreAssetsHelper', array('args' => false));

        return $mock;
    }

    /**
     *
     * @param array $params indexes = params, replace, route
     * @return type
     */
    public function getRouterMock($params = array())
    {
        $routerMock = $this->getServiceMock('\Symfony\Bundle\FrameworkBundle\Routing\Router', array(
            'args' => false,
        ));

        if($params === false)
            return $routerMock;
        
        if(count($params))
            $route = str_replace($params['params'], $params['replace'], $params['route']);
        else
            $route = '/mocked/path/123';

        $this->mockMethod($routerMock, 'generate', $route);

        return $routerMock;
    }

    public function getTranslatorMock()
    {
        return $this->getServiceMock('\Symfony\Bundle\FrameworkBundle\Translation\Translator', array(
            'args' => false,
            'trans' => 'random'
        ));
    }

    public function getFormMock(array $methods = array(), $name = '')
    {
        return $this
                ->getMockBuilder('\Symfony\Component\Form\Form')
                ->setMockClassName($name)
                ->setMethods($methods)
                ->disableOriginalConstructor()
                ->getMock();
    }

    public function getEncoderFactoryMock()
    {
        return $this
                ->getMockBuilder('\Symfony\Component\Security\Core\Encoder\EncoderFactory')
                ->disableOriginalConstructor()
                ->getMock();
    }
    
    public function getPasswordEncoderInterface()
    {
        return $this->getServiceMock('\Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
    }

    public function getEncoderFactory()
    {
        return new \Symfony\Component\Security\Core\Encoder\EncoderFactory(array());
    }
    
    public function getImageMock($id = null)
    {
        if(! $id and $id !== false)
            $id = $this->imageId;

        $imageMock = $this
                ->getMockBuilder('Bundles\AssetBundle\Entity\Image')
                ->getMock();

        if($id)
            $this->mockMethod($imageMock, 'getId', $id);

        return $imageMock;
    }
    
    public function getContainerMock()
    {
        return $this->getServiceMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    }
    
    public function getSwiftMailerMock()
    {
        return $this->getServiceMock('\Swift_Mailer', array('args' => false));
    }
    
    /**
     *
     * @param mixed $id
     * @param type $accountId
     * @return type
     */
    public function getCampaignMock($id = null, $accountId = false)
    {
        if(! $id and $id !== false)
            $id = $this->campaignId;

        $campaignMock = $this->getMock('\Bundles\CampaignsBundle\Entity\Campaign');
            
        if($id)
            $this->mockMethod($campaignMock, 'getId', $id);
        
        // Mock getAccount method and set return value to mocked account object
        $accountMock = null;
        
        if($accountId !== false)
        {
            $accountMock = $this->getAccountMock($accountId);
            
            $this->mockMethod($campaignMock, 'getAccount', $accountMock);
        }

        return $campaignMock;
    }

    public function getRawCampaignMock()
    {
        return $this->getMock('\Bundles\CampaignsBundle\Entity\Campaign');
    }

    public function getAccountMock($id = null)
    {
//        $mock = $this->getEntityMock('AccountBundle:Account');
//        
//        if($id)
//            $this->mockMethod($mock, 'getId', $id);
//        
//        return $mock;
        
        if(! $id and $id !== false)
            $id = $this->accountId;

        $accountMock = $this->getMock('\Bundles\AccountBundle\Entity\Account');

        // Mock getId method for campaign
        if($id)
            $this->mockMethod($accountMock, 'getId', $id);

        $userMock = $this->getUserMock();

        $this->mockMethod($accountMock, 'getOwnerUser', $userMock);

        if($id != 1)
        {
            $parentAccount = $this->getAccountMock(1);

            $this->mockMethod($parentAccount, 'getId', 1);

            $this->mockMethod($accountMock, 'getParentAccount', $parentAccount);
        }

        return $accountMock;
    }
    
    public function getEntityMock($bundleEntity, array $methods = array(), $returnValue = null)
    {
        list($bundle, $entity) = explode(':', $bundleEntity);

        $mock = $this->getServiceMock("\Bundles\\{$bundle}\Entity\\{$entity}", array('args' => false));

        if(count($methods))
        {
            foreach($methods as $method => $value)
            {
                if($returnValue)
                    $this->mockMethod($mock, $value, $returnValue);
                else
                    $this->mockMethod($mock, $method, $value);
            }
        }
        
        if(! $mock)
            $this->throwException (\Exception('Unable to create entity for '. $bundleEntity));

        return $mock;
    }
    
    public function getAccountMembershipMock($id = null)
    {
        $mock = $this->getEntityMock('AccountBundle:AccountMembership');
        
        if($id)
            $this->mockMethod($mock, 'getId', $id);
        
        return $mock;
    }
    
    public function getUserMock($id = null)
    {
        $mock = $this->getEntityMock('UserBundle:User');
        
        if($id)
            $this->mockMethod($mock, 'getId', $id);
        
        return $mock;
    }

    public function getAffiliateNetworkMock($id = null)
    {
        $mock = $this->getEntityMock('AffiliateNetworkBundle:AffiliateNetwork');
        
        if($id)
            $this->mockMethod($mock, 'getId', $id);
        
        return $mock;
    }

    public function getAffiliateServiceMock() 
    {
        return $this->getServiceMock('\Bundles\AffiliateNetworkBundle\Service\AffiliateService', array('args' => false));
    }

    public function getEndUserMock($id = null)
    {
        $mock = $this->getEntityMock('EndUserBundle:User');

        if($id)
            $this->mockMethod($mock, 'getId', $id);

        return $mock;
    }

    public function getParameterBagMock()
    {
        return $this->getServiceMock('\Symfony\Component\HttpFoundation\ParameterBag');
    }

    public function getResponseHeaderBagMock()
    {
        return $this->getServiceMock('Symfony\Component\HttpFoundation\ResponseHeaderBag', array(
            'args' => false
        ));
    }

    public function getGoalMock($id = null, $methods = array())
    {
        if(! $id and $id !== false)
            $id = $this->goalId;

        $goalMock = $this->getMock('\Bundles\GoalBundle\Entity\Goal', $methods);

        if($id)
            $this->mockMethod($goalMock, 'getId', $id);

        return $goalMock;
    }

    public function getRewardMock($id = null)
    {
        if(! $id and $id !== false)
            $id = $this->rewardId;

        $rewardMock = $this->getMock('\Bundles\GoalBundle\Entity\Reward');

        if($id)
            $this->mockMethod($rewardMock, 'getId', $id);

        return $rewardMock;
    }

    public function getEmailMock($id = null, array $methods = array())
    {
        if(! $id and $id !== false)
            $id = $this->emailId;

        $emailMock = $this->getMock('\Bundles\MailerBundle\Entity\Email', $methods);

        if($id)
            $this->mockMethod($emailMock, 'getId', $id);

        return $emailMock;
    }

    public function getEmailTypeMock($id = null)
    {
        if(! $id and $id !== false)
            $id = $this->emailTypeId;

        $emailTypeMock = $this->getMock('\Bundles\MailerBundle\Entity\EmailType');

        if($id)
            $this->mockMethod($emailTypeMock, 'getId', $id);

        return $emailTypeMock;
    }

    public function getMembershipMock($id = null)
    {
        if(! $id and $id !== false)
            $id = $this->membership;

        $membershipMock = $this->getMock('\Bundles\EndUserBundle\Entity\Membership');

        if($id !== false)
        {
            $this->mockMethod($membershipMock, 'getEndUser', $this->getEndUserMock());
            $this->mockMethod($membershipMock, 'getCampaign', $this->getCampaignMock());
            $this->mockMethod($membershipMock, 'getCampaignId', $id);
        }

        return $membershipMock;
    }
    
    public function getRedirectResponseMock()
    {
        $redirectResponseMock = $this->getServiceMock('\Symfony\Component\HttpFoundation\RedirectResponse', array(
            'args' => false
        ));

        $redirectResponseMock->headers = $this->getServiceMock('\Symfony\Component\HttpFoundation\ResponseHeaderBag', array('args' => false));

        $this->setMock($redirectResponseMock->headers)
                ->mockMultiple(array(
                    'setCookie' => true,
                    'clearCookie' => true
                ));

        return $redirectResponseMock;
    }
    
    public function getResponseMock()
    {
        return $this->getServiceMock('\Symfony\Component\HttpFoundation\Response', array('args' => false));
    }

    public function getRequestMock(array $params = array())
    {
        if(! count($params))
        {
            $params = array(
                'host' => 'test.fnd.st',
                'scheme' => 'http'
            );
        }

        $requestMock = $this->getServiceMock('\Symfony\Component\HttpFoundation\Request');
        $requestMock->cookies = $this->getServiceMock('Symfony\Component\HttpFoundation\ParameterBag');

        $this->setMock($requestMock)
                ->mockMultiple(array(
                    'has' => true,
                    'getHttpHost' => $params['host'],
                    'getScheme' => $params['scheme']
                ));

        return $requestMock;
    }

    public function getCurlMock()
    {
        return $this->getServiceMock('\Bundles\CoreBundle\Service\CurlService', array('args' => false));
    }

    public function getSoapMock()
    {
        return $this->getServiceMock('\Soapclient', array(
            'WSDL_URL' => "http://ws.webgains.com/aws.php"
        ));
    }
    
    public function getFormErrorMock()
    {
        return $this->getServiceMock('Symfony\Component\Form\FormError');
    }
    
    public function getSecurityContextMock()
    {
        return $this->getServiceMock('\Symfony\Component\Security\Core\SecurityContextInterface');
    }
    
    public function getTokenMock()
    {
        return $this->getServiceMock('\Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken');
    }

    public function getNewSampleImage()
    {
        $imageString = 'iVBORw0KGgoAAAANSUhEUgAAABwAAAASCAMAAAB/2U7WAAAABl' .
            'BMVEUAAAD///+l2Z/dAAAASUlEQVR4XqWQUQoAIAxC2/0vXZDr' .
            'EX4IJTRkb7lobNUStXsB0jIXIAMSsQnWlsV+wULF4Avk9fLq2r' .
            '8a5HSE35Q3eO2XP1A1wQkZSgETvDtKdQAAAABJRU5ErkJggg==';

        return base64_decode($imageString);
    }

    public function getKernel()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        return $kernel;
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
    
    public function prex()
    {
        $arguments = func_get_args();
        
        \Bundles\CoreBundle\Controller\ApplicationController::prex($arguments);
    }
}