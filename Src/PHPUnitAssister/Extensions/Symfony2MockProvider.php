<?php

namespace PHPUnitAssister\Extensions;

use PHPUnitAssister\Core\Mocker;

/*
 * This class provides standard symfony2.3 mock objects
 */
class Symfony2MockProvider extends Mocker{
    
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
    
    

    public function getEntityManagerMock()//$entityColonBundle = null, $methods = array()
    {
        $entityManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
//                    ->setMethods($methods)
                    ->disableOriginalConstructor()
                    ->getMock();

        $this->setmo($entityManagerMock)
                ->mm('persist');

        // So the entity manager has a real get repository function from the original entity
//        if($entityColonBundle)
//        {
//            $entityMock = $this->getEntityMock($entityColonBundle, array(
//                'getId' => 1
//            ));
//
//            $repoMock = $this->getRepositoryMock($entityColonBundle, array(), $entityMock);
//            $this->mockMethod($entityManagerMock, 'getRepository', $repoMock);
//        }

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
            $this->setmo($repo)
                    ->mmx(array(
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

        $this->setmo($routerMock)
                ->mm('generate', ['will' => $this->returnValue($route)]);

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
    
    public function getContainerMock()
    {
        return $this->getServiceMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    }
    
    public function getSwiftMailerMock()
    {
        return $this->getServiceMock('\Swift_Mailer', array('args' => false));
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
                {
                    $this->setmo($mock)->mm($value, ['will' => $this->returnValue($returnValue)]);
                }
                else
                {
                    $this->setmo($mock)->mm($method, ['will' => $this->returnValue($value)]);
                }
            }
        }
        
        if(! $mock)
        {
            $this->throwException (\Exception('Unable to create entity for '. $bundleEntity));
        }

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
    
    public function getRedirectResponseMock()
    {
        $redirectResponseMock = $this->getServiceMock('\Symfony\Component\HttpFoundation\RedirectResponse', array(
            'args' => false
        ));

        $redirectResponseMock->headers = $this->getServiceMock('\Symfony\Component\HttpFoundation\ResponseHeaderBag', array('args' => false));

        $this->setmo($redirectResponseMock->headers)
                ->mmx(array(
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

        $this->setmo($requestMock)
                ->mmx(array(
                    'getHttpHost' => $params['host'],
                    'getScheme' => $params['scheme']
                ));

        return $requestMock;
    }

    public function getFormErrorMock()
    {
        return $this->getServiceMock('Symfony\Component\Form\FormError');
    }
    
    public function getSecurityContextMock()
    {
        return $this->getServiceMock('\Symfony\Component\Security\Core\SecurityContextInterface');
    }

    public function getPasswordEncoderMock()
    {
        return $this->getServiceMock('\Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
    }
    
    public function getTokenMock()
    {
        return $this->getServiceMock('\Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken');
    }

    public function getKernel()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        return $kernel;
    }

    public function getJsonResponseMock()
    {
        return $this->getServiceMock('Symfony\Component\HttpFoundation\JsonResponse');
    }
}