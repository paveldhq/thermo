<?php


namespace Thermo\Helpers;

use RecursiveDirectoryIterator as dIterator;
use RecursiveIteratorIterator as iIterator;
use Thermo\Dto\RepositoryDescriptor;
use Thermo\Traits\LoggerTrait;

/**
 * Class FileSystemHelper
 * @package Thermo\Helpers
 */
class FileSystemHelper
{

    use LoggerTrait;
    const BASE_WORK_DIR = '/tmp';

    /**
     * @var int
     */
    private int $dirMode;

    /**
     * FileSystemHelper constructor.
     * @param int $dirMode
     */
    public function __construct(int $dirMode)
    {
        $this->dirMode = $dirMode;
    }

    public function deploy(RepositoryDescriptor $descriptor, string $deployDir): void
    {
        $this->getLogger()->debug($descriptor->getUri());
        $tmpDir = vsprintf(
            '%s/%s',
            [
                self::BASE_WORK_DIR,
                $this->generateFolder($descriptor->getUri())
            ]
        );

        $this->tryMkDir($tmpDir, $this->dirMode);
        $this->getUnpackedSources($descriptor->getUri(), $tmpDir);
        $dirContents = scandir($tmpDir);
        $this->copyr(
            vsprintf('%s/%s', [$tmpDir, end($dirContents)]),
            vsprintf('%s/%s', [PROJ_DIR, $deployDir])
        );
    }

    /**
     * @param string $uri
     * @return string
     */
    private function generateFolder(string $uri): string
    {
        return substr(md5($uri), 0, 8);
    }

    /**
     * @param string $name
     * @param int    $mode
     * @param bool   $recursively
     * @return bool
     */
    protected function tryMkDir(string $name, $mode, $recursively = true): bool
    {
        $result = false;
        if (!file_exists($name)) {
            $result = mkdir($name, $mode, $recursively);
        }
        return $result;
    }

    /**
     * @param string $uri
     * @param string $destination
     */
    private function getUnpackedSources(string $uri, string $destination): void
    {
        $this->tryMkDir($destination, $this->dirMode);
        $execString = vsprintf('curl -L %s | tar -xz -C %s', [$uri, $destination]);
        shell_exec($execString);
    }

    protected function copyr($source, $dest)
    {
        $this->tryMkDir($dest, $this->dirMode);
        foreach (
            $iterator = new iIterator(
                new dIterator($source, dIterator::SKIP_DOTS), iIterator::SELF_FIRST
            ) as $item
        ) {
            $targetPathPart = vsprintf('%s/%s', [$dest, $iterator->getSubPathName()]);
            if ($item->isDir()) {
                $this->tryMkDir($targetPathPart, $this->dirMode);
            } else {
                copy($item, $targetPathPart);
            }
        }
    }
}
