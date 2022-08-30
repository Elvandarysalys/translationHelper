<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Elvandar\TranslationHelper\Command\CheckTranslationsCommand;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(CheckTranslationsCommand::class)
        ->arg('$translatorFolder', '%translator.default_path%');
};


