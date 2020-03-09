<?php

namespace Thermo\Commands\Libraries;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thermo\Commands\RepositoryCommandAbstract;

/**
 * Class AddLibraryCommand
 * @package Thermo\Commands\Libraries
 */
class AddLibraryCommand extends RepositoryCommandAbstract
{

    const COMMAND_NAME = 'install';

    /**
     * @var string
     */
    protected string $commandPrefix = 'ext-lib';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(vsprintf('%s:%s', [$this->commandPrefix, static::COMMAND_NAME]));
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initDescriptor($input);
        $this->tryFixTagName();
        $this->tryOverwriteDeployDir();
        $this->fsHelper->deploy($this->getDescriptor(), $this->getDeployDir());
        return 0;
    }

    protected function tryOverwriteDeployDir()
    {
        $this->setDeployDir(
            vsprintf(
                '%s/%s',
                [
                    $this->getDeployDir(),
                    str_replace('/', '_', $this->getDescriptor()->getRepository())
                ]
            )
        );
    }
}
