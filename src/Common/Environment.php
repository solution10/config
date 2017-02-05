<?php

namespace Solution10\Config\Common;

/**
 * Trait Environment
 *
 * Handles getting/setting Environment
 *
 * @package     Solution10\Config\Common
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait Environment
{
    /**
     * @var     string
     */
    protected $environment = 'production';

    /**
     * Setting the environment to read from. Note that this invalidates all loaded
     * config so far, so if you've used values before changing this they might
     * now be different. You've been warned!
     *
     * @param   string  $environment
     * @return  $this
     */
    public function setEnvironment(string $environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Returns the environment that the config is using.
     *
     * @return  string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
}
