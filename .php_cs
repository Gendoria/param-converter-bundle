<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in('DependencyInjection')
    ->in('Request')
    ->in('Resources')
    ;

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->finder($finder);