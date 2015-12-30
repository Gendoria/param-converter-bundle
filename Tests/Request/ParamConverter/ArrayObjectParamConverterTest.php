<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Gendoria\ParamConverterBundle\Tests\Request\ParamConverter;

use ArrayObject;
use Gendoria\ParamConverterBundle\Request\ParamConverter\ArrayObjectParamConverter;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests for ArrayObjectParamConverter
 *
 * @author Tomasz Struczyński <tomasz.struczynski@isobar.com>
 */
class ArrayObjectParamConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Array object param converter.
     * 
     * @var ArrayObjectParamConverter
     */
    private $converter;
    
    public function setUp()
    {
        $this->converter = new ArrayObjectParamConverter();
    }
    
    public function testSupports()
    {
        $config = $this->createConfiguration('ArrayObject');
        $this->assertTrue($this->converter->supports($config));
        
        $config = $this->createConfiguration('DateTime');
        $this->assertFalse($this->converter->supports($config));
        
        $config = $this->createConfiguration();
        $this->assertFalse($this->converter->supports($config));        
    }
    
    public function testApply()
    {
        $request = new Request(array(), array(), array('dummy' => '1'));
        $config = $this->createConfiguration('ArrayObject', 'dummy');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf('ArrayObject', $request->attributes->get('dummy'));
        $object = $request->attributes->get('dummy');
        $this->assertCount(1, $object);
        $this->assertEquals(1, $object[0]);
    }
    
    public function testApplyMultiple()
    {
        $request = new Request(array(), array(), array('dummy' => '1,2,3'));
        $config = $this->createConfiguration('ArrayObject', 'dummy');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf('ArrayObject', $request->attributes->get('dummy'));
        /* @var $object ArrayObject */
        $object = $request->attributes->get('dummy');
        $this->assertCount(3, $object);
        $this->assertEquals(array(1,2,3), $object->getArrayCopy());
    }

    public function testApplyNoParam()
    {
        $request = new Request(array(), array(), array('dummy2' => '1'));
        $config = $this->createConfiguration('ArrayObject', 'dummy');

        $result = $this->converter->apply($request, $config);
        $this->assertFalse($result);
    }
    
    public function testApplyEmpty()
    {
        //Test optional
        $request = new Request(array(), array(), array('dummy' => ''));
        $config = $this->createConfiguration('ArrayObject', 'dummy', array(), true);
        
        $result = $this->converter->apply($request, $config);
        $this->assertFalse($result);
        
        //Test no optional
        $request = new Request(array(), array(), array('dummy' => ''));
        $config = $this->createConfiguration('ArrayObject', 'dummy', array());

        $this->converter->apply($request, $config);
        $this->assertInstanceOf('ArrayObject', $request->attributes->get('dummy'));
        $object = $request->attributes->get('dummy');
        $this->assertCount(0, $object);
    }
    
    public function testApplyCustomDelimiter()
    {
        $request = new Request(array(), array(), array('dummy' => '1,|2'));
        $config = $this->createConfiguration('ArrayObject', 'dummy', array('delimiter' => '|'), true);
        
        $this->converter->apply($request, $config);
        $this->assertInstanceOf('ArrayObject', $request->attributes->get('dummy'));
        /* @var $object ArrayObject */
        $object = $request->attributes->get('dummy');
        $this->assertCount(2, $object);
        $this->assertEquals(array('1,', 2), $object->getArrayCopy());
    }
    
    public function testApplyOnArray()
    {
        $this->setExpectedException('InvalidArgumentException');
        $request = new Request(array(), array(), array('dummy' => array(1)));
        $config = $this->createConfiguration('ArrayObject', 'dummy');

        $this->converter->apply($request, $config);
    }
    
    public function testApplyOnObject()
    {
        $this->setExpectedException('InvalidArgumentException');
        $request = new Request(array(), array(), array('dummy' => new \stdClass()));
        $config = $this->createConfiguration('ArrayObject', 'dummy');

        $this->converter->apply($request, $config);
    }    
    
    public function createConfiguration($class = null, $name = null, array $options = array(), $isOptional = false)
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
        if ($isOptional) {
            $config->expects($this->any())
                   ->method('isOptional')
                   ->will($this->returnValue(true));
        }

        return $config;
    }    
}
