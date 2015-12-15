<?php

/*
 * All rights reserved
 * Copyright 2015 Isobar Poland
 */

namespace Gendoria\ParamConverterBundle\Tests\Request\ParamConverter;

use Gendoria\ParamConverterBundle\Request\ParamConverter\ServiceParamConverter;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of ServiceParamConverterTest
 *
 * @author Tomasz Struczyński <tomasz.struczynski@isobar.com>
 */
class ServiceParamConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Dependency injection container.
     * 
     * @var Container
     */
    private $container;
    
    /**
     * Parameter converter.
     * 
     * @var ServiceParamConverter
     */
    private $converter;

    public function setUp()
    {
        $this->container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
        $this->converter = new ServiceParamConverter($this->container);
    }
    
    public function testSupports()
    {
        $this
            ->container
            ->expects($this->at(0))
            ->method('has')
            ->with('dummy')
            ->will($this->returnValue(true));
        $this
            ->container
            ->expects($this->at(1))
            ->method('has')
            ->with('dummy2')
            ->will($this->returnValue(false));
        
        
        $config = $this->createConfiguration(null, null, array(
            'service' => 'dummy',
            'method' => 'dummy',
        ));
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(null, null, array(
            'service' => 'dummy2',
            'method' => 'dummy',
        ));
        $this->assertFalse($this->converter->supports($config));


        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($this->converter->supports($config));

        $config = $this->createConfiguration();
        $this->assertFalse($this->converter->supports($config));
    }

    public function testApply()
    {
        $service = $this->getMock('stdClass', array('dummy'));
        $service->expects($this->once())
            ->method('dummy')
            ->will($this->returnValue(1));
        
        $this
            ->container
            ->expects($this->at(0))
            ->method('get')
            ->with('dummy')
            ->will($this->returnValue($service));
        
        
        $request = new Request(array(), array(), array('dummy' => '1'));
        $config = $this->createConfiguration(null, 'dummyparam', array(
            'service' => 'dummy',
            'method' => 'dummy',
        ));

        $this->converter->apply($request, $config);

        $this->assertEquals(1, $request->attributes->get('dummyparam'));
    }  
    
    public function testApplyArguments()
    {
        $service = $this->getMock('stdClass', array('dummy'));
        $service->expects($this->once())
            ->method('dummy')
            ->with(1)
            ->will($this->returnArgument(0));
        
        $this
            ->container
            ->expects($this->at(0))
            ->method('get')
            ->with('dummy')
            ->will($this->returnValue($service));
        
        
        $request = new Request(array(), array(), array());
        $config = $this->createConfiguration(null, 'dummyparam', array(
            'service' => 'dummy',
            'method' => 'dummy',
            'arguments' => array(
                '1'
            )
        ));

        $this->converter->apply($request, $config);

        $this->assertEquals(1, $request->attributes->get('dummyparam'));
    }  
    
    public function testApplyRequestArguments()
    {
        $service = $this->getMock('stdClass', array('dummy'));
        $service->expects($this->once())
            ->method('dummy')
            ->with(1)
            ->will($this->returnArgument(0));
        
        $this
            ->container
            ->expects($this->at(0))
            ->method('get')
            ->with('dummy')
            ->will($this->returnValue($service));
        
        
        $request = new Request(array('rp' => 1), array(), array());
        $config = $this->createConfiguration(null, 'dummyparam', array(
            'service' => 'dummy',
            'method' => 'dummy',
            'arguments' => array(
                '%rp%'
            )
        ));

        $this->converter->apply($request, $config);

        $this->assertEquals(1, $request->attributes->get('dummyparam'));
    }
    
    public function testApplyRequestService()
    {
        $intService = $this->getMock('stdClass', array('dummy'));
        $service = $this->getMock('stdClass', array('dummy'));
        $service->expects($this->once())
            ->method('dummy')
            ->with($intService)
            ->will($this->returnValue(1));
        
        $this
            ->container
            ->expects($this->at(0))
            ->method('has')
            ->with('dummy2')
            ->will($this->returnValue(true));
        
        $this
            ->container
            ->expects($this->at(1))
            ->method('get')
            ->with('dummy2')
            ->will($this->returnValue($intService));

        $this
            ->container
            ->expects($this->at(2))
            ->method('get')
            ->with('dummy')
            ->will($this->returnValue($service));
        
        $request = new Request(array(), array(), array());
        $config = $this->createConfiguration(null, 'dummyparam', array(
            'service' => 'dummy',
            'method' => 'dummy',
            'arguments' => array(
                '@dummy2'
            )
        ));

        $this->converter->apply($request, $config);

        $this->assertEquals(1, $request->attributes->get('dummyparam'));
    }  
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testApplyRequestNonExistentService()
    {
        $this
            ->container
            ->expects($this->at(0))
            ->method('has')
            ->with('dummy2')
            ->will($this->returnValue(false));
        
        $request = new Request(array(), array(), array());
        $config = $this->createConfiguration(null, 'dummyparam', array(
            'service' => 'dummy',
            'method' => 'dummy',
            'arguments' => array(
                '@dummy2'
            )
        ));

        $this->converter->apply($request, $config);
    }
    
    public function testApplyIncorrectType()
    {
        $service = $this->getMock('stdClass', array('dummy'));
        $service->expects($this->once())
            ->method('dummy')
            ->will($this->returnValue(1));
        
        $this
            ->container
            ->expects($this->at(0))
            ->method('get')
            ->with('dummy')
            ->will($this->returnValue($service));
        
        
        $request = new Request(array(), array(), array('dummy' => '1'));
        $config = $this->createConfiguration('NonExistentClass', 'dummyparam', array(
            'service' => 'dummy',
            'method' => 'dummy',
        ));

        $this->assertFalse($this->converter->apply($request, $config));
    }
    
    public function createConfiguration($class = null, $name = null, array $options = array())
    {
        $config = $this
            ->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
            ->setMethods(array('getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray', 'isOptional'))
            ->disableOriginalConstructor()
            ->getMock();

        if ($name !== null) {
            $config->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue($name));
        }
        if ($class !== null) {
            $config->expects($this->any())
                   ->method('getClass')
                   ->will($this->returnValue($class));
        }
        if ($options !== array()) {
            $config->expects($this->any())
                   ->method('getOptions')
                   ->will($this->returnValue($options));
        }

        return $config;
    }    
}
