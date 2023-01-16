<?php

namespace App;

use Exception;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application
{
    private BaseApplication $application;

    public function __construct(
        private readonly ContainerInterface $container,
    )
    {
        $this->application = new BaseApplication();
    }

    /**
     * @throws Exception
     */
    public function run(InputInterface $input): int
    {
        $this->application->add($this->container->get('App\Command\RunCommand'));
        $this->application->add($this->container->get('App\Command\WatchCommand'));

        return $this->application->run($input);
    }

    public static function getBaseDir(): string
    {
        return realpath('./');
    }
}
