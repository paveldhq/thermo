<?php

namespace Thermo\Helpers;

use Github\Client;
use Thermo\Dto\RepositoryDescriptor;
use Thermo\Traits\LoggerTrait;

/**
 * Class GithubHelper
 * @package Thermo\Helpers
 */
class GithubHelper
{

    use LoggerTrait;
    /**
     * @var Client
     */
    private Client $client;
    /**
     * @var RepositoryDescriptor
     */
    private RepositoryDescriptor $repo;

    public function __construct(Client $client)
    {
        $this->setClient($client);
    }

    /**
     * @param RepositoryDescriptor $repositoryDescriptor
     * @return GithubHelper
     */
    public function setRepo(RepositoryDescriptor $repositoryDescriptor): self
    {
        $this->repo = $repositoryDescriptor;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastReleaseTag(): string
    {
        $release = $this
            ->getClient()
            ->api('repo')
            ->releases()
            ->latest(
                $this->repo->getRepoUser(),
                $this->repo->getRepoName()
            );

        return $release['tag_name'];
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $targetTag
     * @return string
     */
    public function getTagTarballUri(string $targetTag): string
    {
        foreach ($this->getRepoTags() as $tag) {
            if ($tag['name'] === $targetTag) {
                return $tag['tarball_url'];
            }
        }
    }

    /**
     * @return array
     */
    private function getRepoTags(): array
    {
        return $this->getClient()
                    ->api('repo')
                    ->tags(
                        $this->repo->getRepoUser(),
                        $this->repo->getRepoName()
                    );
    }

    /**
     * @param string $branch
     * @return string
     */
    public function getTarBallUriBranch($branch = 'master'): string
    {
        return vsprintf(
            'https://github.com/%s/%s/tarball/%s',
            [
                $this->repo->getRepoUser(),
                $this->repo->getRepoName(),
                $branch
            ]
        );
    }

}
