<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Gendoria\ParamConverterBundle\Request\ParamConverter;

use ArrayObject;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * This param converter converts parameters to array objects.
 *
 * Parameter has to be string of values separated with delimiter. By default
 * delimiter is comma, but you can set your own by passing options.
 *
 * Param converter invocation:
 *
 * `@ParamConverter("parameter_name")`
 *
 * With custom delimiter:
 *
 * `@ParamConverter("parameter_name", options={"delimiter" = "|"})`
 *
 * @author Tomasz Struczyński <tomasz.struczynski@isobar.com>
 */
class ArrayObjectParamConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException Thrown, when parameter has been already parsed to array or object.
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $param = $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $options = $configuration->getOptions();
        $value = $request->attributes->get($param);

        if (!$value && $configuration->isOptional()) {
            return false;
        }

        if (is_object($value) || is_array($value)) {
            throw new InvalidArgumentException('Parameter already parsed to object or array.');
        }

        if (!empty($options['delimiter'])) {
            $delimiter = $options['delimiter'];
        } else {
            $delimiter = ',';
        }

        $array = $this->getArrayObject($delimiter, $value);
        $request->attributes->set($param, $array);

        return true;
    }

    /**
     * Get array object instance based on parameter value.
     *
     * @param string $delimiter
     * @param string $value
     *
     * @return ArrayObject
     */
    private function getArrayObject($delimiter, $value)
    {
        if (!empty($value)) {
            $arrayValues = explode($delimiter, $value);
        } else {
            $arrayValues = array();
        }

        return new ArrayObject($arrayValues);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return 'ArrayObject' === $configuration->getClass();
    }
}
