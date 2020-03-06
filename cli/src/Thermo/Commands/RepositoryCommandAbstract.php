<?php


namespace Thermo\Commands;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thermo\Dto\RepositoryDescriptor;
use Thermo\Helpers\FileSystemHelper;
use Thermo\Helpers\GithubHelper;
use Thermo\Traits\LoggerTrait;

/**
 * Class RepositoryCommandAbstract
 * @package Thermo\Commands
 */
abstract class RepositoryCommandAbstract extends Command
{

    use LoggerTrait;

    const CONSOLE_ATTRIBUTE_REPOSITORY = 'repository';
    const CONSOLE_ATTRIBUTE_VERSION    = 'version';

    const COMMAND_MODE          = 'mode';
    const COMMAND_DESCRIPTION   = 'description';
    const COMMAND_DEFAULT_VALUE = 'default';

    const COMMAND_SETTINGS = [
        'attributes' => [
            self::CONSOLE_ATTRIBUTE_REPOSITORY => [
                self::COMMAND_MODE        => InputArgument::REQUIRED,
                self::COMMAND_DESCRIPTION => 'Target repository',
            ],
            self::CONSOLE_ATTRIBUTE_VERSION    => [
                self::COMMAND_MODE          => InputArgument::OPTIONAL,
                self::COMMAND_DESCRIPTION   => 'Target tag, default - latest release.',
                self::COMMAND_DEFAULT_VALUE => null
            ]
        ]
    ];
    /**
     * @var FileSystemHelper
     */
    protected FileSystemHelper $fsHelper;
    /**
     * @var GithubHelper
     */
    protected GithubHelper $ghHelper;
    /**
     * @var string
     */
    protected string $deployDir = '';
    private RepositoryDescriptor $descriptor;

    /**
     * RepositoryCommandAbstract constructor.
     * @param LoggerInterface  $logger
     * @param GithubHelper     $ghHelper
     * @param FileSystemHelper $fsHelper
     */
    public function __construct(GithubHelper $ghHelper, FileSystemHelper $fsHelper)
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
        $this->setDescription('Downloads latest release of repository');
        $this->injectAttributes();
    }

    private function injectAttributes(): void
    {
        foreach (static::COMMAND_SETTINGS['attributes'] as $attributeName => $attributeSettings) {
            if (array_key_exists(self::COMMAND_DEFAULT_VALUE, $attributeSettings)) {
                $this->addArgument(
                    $attributeName,
                    $attributeSettings[self::COMMAND_MODE],
                    $attributeSettings[self::COMMAND_DESCRIPTION],
                    $attributeSettings[self::COMMAND_DEFAULT_VALUE]
                );
            } else {
                $this->addArgument(
                    $attributeName,
                    $attributeSettings[self::COMMAND_MODE],
                    $attributeSettings[self::COMMAND_DESCRIPTION]
                );
            }
        }
    }

    /**
     * @param RepositoryDescriptor $repo
     * @return string
     */
    protected function getLatestTag(RepositoryDescriptor $repo): string
    {
        $this->ghHelper->setRepo($repo);
        return $this->ghHelper->getLastReleaseTag();
    }

    protected function getTagUri(string $tag): string
    {
        return $this->ghHelper->getTagTarballUri($tag);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initDescriptor($input);


        $this->getLogger()->debug(var_export($this->getDescriptor(), true));

        $this->tryFixTagName();


        $this->fsHelper->deploy($this->getDescriptor(), $this->getDeployDir());

        return 0;
    }

    protected function initDescriptor(InputInterface $input): void
    {
        $this->setDescriptor(
            new RepositoryDescriptor(
                $input->getArgument(self::CONSOLE_ATTRIBUTE_REPOSITORY),
                $input->getArgument(self::CONSOLE_ATTRIBUTE_VERSION)
            )
        );
    }

    /**
     * @return RepositoryDescriptor
     */
    public function getDescriptor(): RepositoryDescriptor
    {
        return $this->descriptor;
    }

    /**
     * @param RepositoryDescriptor $descriptor
     */
    public function setDescriptor(RepositoryDescriptor $descriptor): void
    {
        $this->descriptor = $descriptor;
    }

    protected function tryFixTagName(): void
    {
        try {
            $this->getLogger()->debug('No tag defiled,  searching for latest...');
            if (null === $this->getDescriptor()->getTag()) {
                $this->getDescriptor()
                     ->setTag(
                         $this
                             ->ghHelper
                             ->setRepo($this->getDescriptor())
                             ->getLastReleaseTag()
                     );
                $this->getLogger()->debug(vsprintf('Found latest tag: %s', [$this->getDescriptor()->getTag()]));
            }

            $this->getDescriptor()->setUri(
                $this->ghHelper->setRepo($this->getDescriptor())->getTagTarballUri($this->getDescriptor()->getTag())
            );
        } catch (Exception $e) {
            if (null === $this->getDescriptor()->getTag()) {
                $this->getLogger()->debug('No tag found,  downloading master branch...');
                // get
                $this->getDescriptor()->setUri($this->ghHelper->setRepo($this->getDescriptor())->getTarBallUriBranch());
            }
        }
    }

    /**
     * @return string
     */
    public function getDeployDir(): string
    {
        return $this->deployDir;
    }

    /**
     * @param string $deployDir
     */
    public function setDeployDir(string $deployDir): void
    {
        $this->deployDir = $deployDir;
    }

}
