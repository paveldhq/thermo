<?php


namespace Thermo\Helpers;

use RecursiveDirectoryIterator as dIterator;
use RecursiveIteratorIterator as iIterator;

/**
 * Class FileSystemHelper
 * @package Thermo\Helpers
 */
class FileSystemHelper
{

    /**
     * @var int
     */
    private int $dirMode;

    /**
     * @var string
     */
    private string $workDir;

    /**
     * @var string
     */
    private string $srcDir;

    /**
     * FileSystemHelper constructor.
     * @param int $dirMode
     * @param string $workDir
     * @param string $srcDir
     */
    public function __construct(int $dirMode, string $workDir, string $srcDir)
    {
        $this->dirMode = $dirMode;
        $this->workDir = $workDir;
        $this->srcDir = $srcDir;
    }

    /**
     * @param string $name
     * @param int $mode
     * @param bool $recursively
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

    public function downloadSources(string $uri): void
    {
        $this->tryMkDir($this->workDir, $this->dirMode);
        $this->getUnpackedSources($uri, $this->workDir);
        $dirContents = scandir($this->workDir);
        $this->copyr(vsprintf('%s/%s', [$this->workDir, end($dirContents)]), $this->srcDir);
    }
}
