Summary
=======

Gendoria parameter converter bundle adds some more converters to Framework Extra Bundle converters.

[![Build Status](https://img.shields.io/travis/Gendoria/param-converter-bundle/master.svg)](https://travis-ci.org/Gendoria/param-converter-bundle)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/Gendoria/param-converter-bundle.svg)](https://scrutinizer-ci.com/g/Gendoria/param-converter-bundle/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Gendoria/param-converter-bundle.svg)](https://scrutinizer-ci.com/g/Gendoria/param-converter-bundle/?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/gendoria/param-converter-bundle.svg)](https://packagist.org/packages/gendoria/param-converter-bundle)
[![Latest Stable Version](https://img.shields.io/packagist/v/gendoria/param-converter-bundle.svg)](https://packagist.org/packages/gendoria/param-converter-bundle)

Bundle should be compatible with all versions of PHP higher, than 5.4 (check the build status).

Installation
============

Step 1: Download the bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require gendoria/param-converter "~1"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Framework Extra bundle
-------------------------

This bundle requires Framework Extra bundle enabled. You can to that
 by adding it to the list of registered bundles in the `app/AppKernel.php` 
file of your project (if not already done):

```php
<?php
// app/AppKernel.php
// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
        );
        // ...
    }
    // ...
}
```

Step 3: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php
// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Gendoria\ParamConverterBundle\GendoriaParamConverterBundle(),
        );
        // ...
    }
    // ...
}
```

Usage
=====


You can use parameter converters from this bundle as any other parameter converters.

Service param converter
-----------------------

You can use it by adding following call:

```
@ParamConverter("parameter_name", converter="service_param_converter", options={"service" = "service_id", "method" = "service_method", "arguments" = {"%requestParamName%", "@otherServiceId", "someParameter"})
```

Where the first argument is a parameter name, converter specifies the converter to use,
and options - configure the converter.

Required options are service (service ID) and method (service method). 
Additionally, you can pass arguments to method by using "arguments" option.

Arguments is a list of service arguments. There are three types of them:

- **Simple argument**. This is the default option. No additional parsing is added.
- **Request parameter**. You have to enclose parameter name with % signs 
  and converter will extract it from the request. For example, 
  when you define argument as `%myParam%`, the service will try to fetch parameter
  `myParam` from the request.
- **Service parameter**. When you preceede argument with `@` character, it will be treated as a service ID.
  Parser will try to fetch the service from service container and inject it to the method call.
  If the service is not registered in the container, an `\InvalidArgumentException` will be thrown.

Converter parameter is only needed, when conversion may collide with other param converters
(especially default `DoctrineParamConverter`).

ArrayObject param converter
---------------------------

This parameter converter can be used to explode parameter into array of objects.
Parameter has to be a string with delimited values. Default delimiter is comma, 
but you can use your own, custom delimiter.

To invoke param converter, you should use following annotation:

```
@ParamConverter("parameter_name")
```

Or with custom delimiter: 

```
@ParamConverter("parameter_name", options={"delimiter" = "|"})
```

Where parameter type in function type hints is `ArrayObject`.
