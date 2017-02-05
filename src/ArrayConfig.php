<?php

namespace Solution10\Config;

use Solution10\Config\Common\Environment;
use Solution10\Config\Common\RecursiveRead;

/**
 * Class ArrayConfig
 *
 * A super-simple config class. Pass in arrays of values and which environment
 * they belong to, then read them back out! Great for unit tests.
 *
 * @package     Solution10\Config
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class ArrayConfig implements ConfigInterface
{
    use Environment {
        setEnvironment as traitSetEnvironment;
    }
    use RecursiveRead;

    /**
     * @var     array
     */
    protected $values = ['_top' => []];

    /**
     * @var     bool
     */
    protected $needsRebuild = false;

    /**
     * @var     array
     */
    protected $buildCache = null;

    /**
     * @param   array   $config
     */
    public function __construct(array $config = [])
    {
        if ($config) {
            $this->addConfig($config);
        }
    }

    /**
     * Setting the environment to read from. Note that this invalidates all loaded
     * config so far, so if you've used values before changing this they might
     * now be different. You've been warned!
     *
     * @param   string $environment
     * @return  $this
     */
    public function setEnvironment(string $environment)
    {
        $this->buildCache = null;
        return $this->traitSetEnvironment($environment);
    }

    /**
     * Adds config into this object
     *
     * @param   array   $config
     * @param   string  $environment
     * @return  $this
     */
    public function addConfig(array $config, string $environment = null)
    {
        $env = ($environment)? $environment : '_top';
        $this->values[$env] = (array_key_exists($env, $this->values))?
            array_replace_recursive($this->values[$env], $config)
            : $config
        ;
        $this->buildCache = null;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        $built = $this->buildConfig();
        return $this->recursiveRead($key, $built, $default);
    }

    /**
     * Takes the $this->values and merges according to our
     * current environment.
     *
     * @return array
     */
    protected function buildConfig(): array
    {
        if ($this->buildCache === null) {
            // Read the top config first:
            $built = [];
            $built = array_replace_recursive($built, $this->values['_top']);

            if (array_key_exists($this->environment, $this->values)) {
                $built = array_replace_recursive($built, $this->values[$this->environment]);
            }

            $this->buildCache = $built;
        }

        return $this->buildCache;
    }
}
