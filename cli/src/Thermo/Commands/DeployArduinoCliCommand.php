<?php

namespace Thermo\Commands;

use Github\Client;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeployArduinoCliCommand
 * @package Thermo\Commands
 */
class DeployArduinoCliCommand extends Command
{
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

    protected function copyr($source, $dest)
    {
        mkdir($dest, 0777, true);
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $source,
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName(), 0777, true);
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $input->getArgument('repository');

        $output->writeln([
            vsprintf('Looking for tags for %s repository...', [$repo]),
        ]);

        list($repoUser, $repoName) = explode('/', $repo);

        $client = new Client();
        $release = $client
            ->api('repo')
            ->releases()
            ->latest($repoUser, $repoName);

        $releaseTag = $release['tag_name'];

        $output->writeln(vsprintf("Latest release has tag: %s", [$releaseTag]));

        $tags = $client
            ->api('repo')
            ->tags($repoUser, $repoName);

        foreach ($tags as $tag) {
            if ($tag['name'] === $releaseTag) {
                break;
            }
        }

        $tarUri = $tag['tarball_url'];

        $tempDir = '/tmp/arduino-cli';

        mkdir($tempDir, 0777, true);


        $execString = vsprintf('curl -L %s | tar -xz -C %s', [$tarUri, $tempDir]);
        shell_exec($execString);
        $contents = scandir($tempDir);

        $srcDir = '/project/build-tools/src';
        //mkdir($srcDir, 0777, true);

        $this->copyr(vsprintf('%s/%s', [$tempDir, end($contents)]), $srcDir);

        return 0;
    }
}
