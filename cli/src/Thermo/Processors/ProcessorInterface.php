<?php


namespace Thermo\Processors;

/**
 * Interface ProcessorInterface
 * @package Thermo\Processors
 */
interface ProcessorInterface
{
    /**
     * @return string
     */
    function process(): string;
}
