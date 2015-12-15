Summary
=======

Gendoria parameter converter bundle adds some more converters to 

[![Build Status](https://travis-ci.org/Gendoria/param-converter-bundle.svg?branch=master)](https://travis-ci.org/Gendoria/param-converter-bundle)


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
