<?php

namespace Thermo\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thermo\Helpers\FileSystemHelper;
use Thermo\Helpers\GithubHelper;

/**
 * Class DeployArduinoCliCommand
 * @package Thermo\Commands
 */
class DeployArduinoCliCommand extends Command
{

    /**
     * @var FileSystemHelper
     */
    private FileSystemHelper $fsHelper;

    /**
     * @var GithubHelper
     */
    private GithubHelper $ghHelper;

    public function __construct(FileSystemHelper $fsHelper, GithubHelper $ghHelper)
    {
        parent::__construct(null);
        $this->fsHelper = $fsHelper;
        $this->ghHelper = $ghHelper;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription('Downloads latest version of {{arduino-cli}}')
            ->addArgument('repository', InputArgument::REQUIRED, 'Target repository');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $input->getArgument('repository');
        $this->ghHelper->setRepo($repo);
        $output->writeln(vsprintf('Looking for tags for %s repository...', [$repo]));
        $lastReleaseTag = $this->ghHelper->getLastReleaseTag();
        $output->writeln(vsprintf("Latest release has tag: %s", [$lastReleaseTag]));
        $releaseUri = $this->ghHelper->getTagTarballUri($lastReleaseTag);
        $output->writeln(vsprintf("Preparing to download %s", [$releaseUri]));
        $this->fsHelper->downloadSources($releaseUri);
        return 0;
    }
}
