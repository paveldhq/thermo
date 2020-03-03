<?php

namespace Thermo\Helpers;

use Github\Client;

/**
 * Class GithubHelper
 * @package Thermo\Helpers
 */
class GithubHelper
{

    /**
     * @var string
     */
    private string $repoUser = '';

    /**
     * @var string
     */
    private string $repoName = '';

    /**
     * @param string $repository
     */
    public function setRepo(string $repository): void
    {
        list($repoUser, $repoName) = explode('/', $repository);

        $this->repoUser = $repoUser;
        $this->repoName = $repoName;
    }

    private function getClient()
    {
        return new Client();
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
            ->latest($this->repoUser, $this->repoName);

        return $release['tag_name'];
    }

    /**
     * @param string $targetTag
     * @return string
     */
    public function getTagTarballUri(string $targetTag): string
    {
        $tags = $this->getClient()
            ->api('repo')
            ->tags($this->repoUser, $this->repoName);

        foreach ($tags as $tag) {
            if ($tag['name'] === $targetTag) {
                return $tag['tarball_url'];
            }
        }
    }
}
