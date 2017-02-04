<?php

namespace Solution10\Config;

/**
 * Class Config
 *
 * Super simple config loader/reader class. Supports inheritance across environments,
 * but not at lot else as the aim is to be wicked fast and light.
 *
 * @package     Solution10\Config
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Config
{
    /**
     * @var     string[]  Paths to the config files
     */
    protected $configPaths = [];

    /**
     * @var     string  Environment folder. Defaults to "production" which is the top-level.
     */
    protected $environment = 'production';

    /**
     * @var     array   Cache for the loaded config
     */
    protected $values = [];

    /**
     * You can pass in an initial set of configuration here, or leave it blank.
     *
     * @param   array       $initialConfig
     */
    public function __construct(array $initialConfig = [])
    {
        $this->addConfig($initialConfig);
    }

    /**
     * Loads config into the working array.
     *
     * @param   array   $config
     * @return  $this
     */
    public function addConfig(array $config)
    {
        $this->values = array_replace_recursive($this->values, $config);
        return $this;
    }

    /**
     * Adds a path to look in for config files. This should be the top level directory as this class
     * will explore downwards for environment specific config.
     *
     * @param   string  $path   Path to the config files
     * @return  $this
     * @throws  Exception
     */
    public function addConfigPath(string $path)
    {
        if (!file_exists($path) || !is_dir($path) || !is_readable($path)) {
            throw new Exception(
                'Invalid or unreadable path: '.$path,
                Exception::INVALID_PATH
            );
        }
        $this->configPaths[] = $path;

        return $this;
    }

    /**
     * Adds multiple paths to search for config in
     *
     * @param   array   $paths
     * @return  $this
     * @throws  Exception
     */
    public function addConfigPaths(array $paths)
    {
        foreach ($paths as $p) {
            $this->addConfigPath($p);
        }
        return $this;
    }

    /**
     * Returns all the config paths we're using
     *
     * @return  string[]
     */
    public function getConfigPaths(): array
    {
        return $this->configPaths;
    }

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

    /**
     * Returns a value from the config. You can also provide a default if that
     * key is not present.
     *
     * Keys are divided into sections with . (periods)
     *
     * A key of person.bike.colour maps to $configPath/person.php['bike']['colour']
     *
     * @param   string  $key        Key path (see above)
     * @param   mixed   $default    Default value. Defaults to null.
     * @return  mixed
     */
    public function get($key, $default = null)
    {
        // Grab the parts:
        $keyparts = explode('.', $key);
        $file = $keyparts[0];
        if (!array_key_exists($file, $this->values)) {
            $this->loadFile($file);
        }

        // If we still don't have the file value, return default
        if ($this->values[$file] === null) {
            return $default;
        }

        // Now the tricky part, recursively read the path from the parts:
        $totalParts = count($keyparts);
        $i = 1;
        $value = $this->values;
        foreach ($keyparts as $part) {
            // If $value is not an array, but we have more parts, then
            // the key doesn't exist. Return default.
            if (!is_array($value) && $i != $totalParts) {
                $value = $default;
                break;
            }

            // Otherwise, set the value:
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                $value = $default;
                break;
            }
            $i ++;
        }

        return $value;
    }

    /**
     * Finds the required files for a given 'namespace'. So if I ask for get('app.name')
     * it would return the full paths to app.php in both the production and specified environments
     * in an array.
     *
     * @param   string  $namespace
     * @return  array
     */
    public function getRequiredFiles($namespace)
    {
        $files = [];

        // First loop is finding 'production' config for each basepath:
        foreach ($this->configPaths as $basePath) {
            $configFileCandidate = $basePath.'/'.$namespace.'.php';
            if (file_exists($configFileCandidate)) {
                $files[] = realpath($configFileCandidate);
            }
        }

        // Second loop is finding 'environment level' config for each basepath:
        if ($this->environment !== 'production') {
            foreach ($this->configPaths as $basePath) {
                $envConfigFileCandidate = $basePath.'/'.$this->environment.'/'.$namespace.'.php';
                if (file_exists($envConfigFileCandidate)) {
                    $files[] = realpath($envConfigFileCandidate);
                }
            }
        }
        return $files;
    }

    /**
     * Load a file, including the environment config if present. If the file
     * does not exist, this won't throw, it'll just quietly insert null into
     * the array, preventing another load attempt.
     *
     * @param   string  $file   File to load
     * @return  void
     */
    protected function loadFile($file)
    {
        $requiredFiles = $this->getRequiredFiles($file);

        $this->addConfig([$file => null]);
        foreach ($requiredFiles as $fileToRequire) {
            $overrideConfig = require $fileToRequire;
            $this->addConfig([$file => $overrideConfig]);
        }
    }
}
