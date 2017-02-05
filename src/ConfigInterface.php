<?php

namespace Solution10\Config;

/**
 * Interface ConfigInterface
 *
 * Common interface for all the Solution10\Config instances.
 *
 * @package     Solution10\Config
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
interface ConfigInterface
{
    /**
     * Setting the environment to read from. Note that this invalidates all loaded
     * config so far, so if you've used values before changing this they might
     * now be different. You've been warned!
     *
     * @param   string  $environment
     * @return  $this
     */
    public function setEnvironment(string $environment);

    /**
     * Returns the environment that the config is using.
     *
     * @return  string
     */
    public function getEnvironment(): string;

    /**
     * Returns a config value by a string key. If the key is not found,
     * the $default value will be returned.
     *
     * @param   string  $key
     * @param   mixed   $default
     * @return  mixed
     */
    public function get(string $key, $default = null);
}
