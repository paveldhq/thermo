<?php

namespace Thermo;

use Symfony\Component\Console\Application;
use Thermo\Commands\DeployArduinoCliCommand;
use Thermo\Commands\GenerateConfigYaml;

class Bootstrap
{
    /**
     * @var Application
     */
    private static Application $app;

    public static function boot(): void
    {
        static::$app = new Application();
        static::$app->add(new DeployArduinoCliCommand());
        static::$app->add(new GenerateConfigYaml());
        static::$app->run();
    }
}
