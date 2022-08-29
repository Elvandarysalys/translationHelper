<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Elvandar\TranslationHelper\Command\CheckTranslationsCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(CheckTranslationsCommand::class)
        ->arg('$translatorFolder', '%translator.default_path%');
};


