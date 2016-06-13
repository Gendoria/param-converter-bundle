<?php

/*
 * All rights reserved
 * Copyright 2015 Gendoria
 */

namespace Gendoria\ParamConverterBundle\Request\ParamConverter;

use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * This param converter converts parameters based on service method call.
 *
 * Param converter invocation:
 *
 * `@ParamConverter("parameter_name", converter="service_param_converter", options={"service" = "service_id", "method" = "service_method", "arguments" = {"%requestParamName%", "@otherServiceId", "someParameter"})`
 *
 * Using this converter you can inject virtually any parameter into a controller, as service method call is not restricted with anything.
 * You should be **very cautious**, though, when passing request parameters into a service method call, as it can potentially lead to injections of malicious code.
 * All of the parameters injected should be properly restricted (eg to integers) using routing configuration.
 *
 * @author Tomasz Struczyński <gendoria@gendoria.pl>
 */
class ServiceParamConverter implements ParamConverterInterface
{
    /**
     * Service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Class constructor.
     *
     * @param Container $container Service container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Apply param converter.
     *
     * @param Request        $request       Request
     * @param ParamConverter $configuration Param converter configuration.
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $param = $configuration->getName();
        $options = $this->getOptions($configuration);

        foreach ($options['arguments'] as &$value) {
            $value = $this->parseArgument($value, $request);
        }

        $service = $this->container->get($options['service']);
        $return = call_user_func_array(array($service, $options['method']), $options['arguments']);

        if ($configuration->getClass() && (!is_object($return) || !is_a($return, $configuration->getClass()))) {
            return false;
        }

        $request->attributes->set($param, $return);

        return true;
    }

    /**
     * Parse single argument.
     *
     * @param string  $value
     * @param Request $request
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    private function parseArgument($value, Request $request)
    {
        if (strpos($value, '%') === 0 && strrpos($value, '%') === strlen($value)-1) {
            return $this->parseParameterArgument(substr($value, 1, strlen($value)-2), $request);
        } elseif (strpos($value, '@') === 0) {
            if ($this->container->has(substr($value, 1)) === false) {
                throw new InvalidArgumentException('Unknown service requested: '.$value);
            }

            return $this->container->get(substr($value, 1));
        }

        return $value;
    }
    
    /**
     * Parse parameter type argument.
     * 
     * @param string $value
     * @param Request $request
     * @return type
     */
    private function parseParameterArgument($value, Request $request)
    {
        if ($this !== $result = $request->get($value, $this)) {
            return $result;
        }
        
        //Argument may refer to function call of another argument
        if (strpos($value, '::')) {
            $paramName = substr($value, 0, strpos($value, '::'));
            $fnData = substr($value, strpos($value, '::')+2);
            if (strpos($fnData, '(')) {
                $fnName = substr($fnData, 0, strpos($fnData, '('));
                $fnArguments = $this->parseFunctionArguments(substr($fnData, strpos($fnData, '(')), $request);
            } else {
                $fnName = $fnData;
                $fnArguments = array();
            }
            $possibleObject = $request->get($paramName);
            if (!$possibleObject || !is_callable(array($possibleObject, $fnName))) {
                return null;
            }
            return call_user_func_array(array($possibleObject, $fnName), $fnArguments);
        }
        return null;
    }
    
    private function parseFunctionArguments($argumentsStr, Request $request)
    {
        if (strpos($argumentsStr, '(') !== 0 || strpos($argumentsStr, ')') !== strlen($argumentsStr)-1) {
            throw new \InvalidArgumentException("Incorrect function arguments string");
        }
        $argumentsArr = explode(",", substr($argumentsStr, 1, strlen($argumentsStr)-2));
        foreach ($argumentsArr as &$argument) {
            $argument = $this->parseArgument($argument, $request);
        }
        return $argumentsArr;
    }

    public function supports(ParamConverter $configuration)
    {
        $options = $this->getOptions($configuration);

        return $this->container->has($options['service']) && !empty($options['method']);
    }

    protected function getOptions(ParamConverter $configuration)
    {
        $options = $configuration->getOptions();
        if (!is_array($options)) {
            $options = array();
        }

        return array_replace(array(
            'service' => null,
            'method' => null,
            'arguments' => array(),
        ), $options);
    }
}
