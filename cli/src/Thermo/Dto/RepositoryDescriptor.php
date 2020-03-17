<?php


namespace Thermo\Dto;

use Symfony\Component\Yaml\Yaml;

/**
 * Class RepositoryDescriptor
 * @package Thermo\Dto
 */
class RepositoryDescriptor
{

    /**
     * @var string
     */
    private string $repository;

    /**
     * @var string
     */
    private string $repoUser;

    /**
     * @var string
     */
    private string $repoName;

    /**
     * @var string|null
     */
    private ?string $tag;

    /**
     * @var string
     */
    private string $uri;

    /**
     * RepositoryDescriptor constructor.
     * @param string      $repository
     * @param string|null $tag
     */
    public function __construct(string $repository, ?string $tag = null)
    {
        $this->repository = $repository;
        $this->tag        = $tag;
        $this->parseRepositoryString();
    }

    /**
     * Parses repository full name {{ user/repo }}
     */
    private function parseRepositoryString(): void
    {
        list($rUser, $rName) = explode('/', $this->repository);
        $this->repoUser = $rUser;
        $this->repoName = $rName;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function getRepository(): string
    {
        return $this->repository;
    }

    /**
     * @param string $repository
     */
    public function setRepository(string $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getRepoUser(): string
    {
        return $this->repoUser;
    }

    /**
     * @param string $repoUser
     */
    public function setRepoUser(string $repoUser): void
    {
        $this->repoUser = $repoUser;
    }

    /**
     * @return string
     */
    public function getRepoName(): string
    {
        return $this->repoName;
    }

    /**
     * @param string $repoName
     */
    public function setRepoName(string $repoName): void
    {
        $this->repoName = $repoName;
    }

    /**
     * @return string|null
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    public function __toString()
    {
        $state = [
            'repository' => $this->repository,
            'tag'        => $this->tag,
        ];

        return Yaml::dump($state);
    }
}
