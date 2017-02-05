<?php

namespace Solution10\Config;

/**
 * Class FilesystemConfig
 *
 * Used for reading arrays of config from the filesystem.
 *
 * @package     Solution10\Config
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class FilesystemConfig implements ConfigInterface
{
    /**
     * @var     string[]  Paths to the config files
     */
    protected $configPaths = [];

    /**
     * @var     array   Cache for the loaded config
     */
    protected $values = [];

    /**
     * @var     ArrayConfig
     */
    protected $config;

    /**
     * You can pass in an initial set of configuration paths here, or leave it blank.
     *
     * @param   array       $initialPaths
     */
    public function __construct(array $initialPaths = [])
    {
        $this->addConfigPaths($initialPaths);
        $this->config = new ArrayConfig();
    }

    /**
     * Setting the environment to read from. Note that this invalidates all loaded
     * config so far, so if you've used values before changing this they might
     * now be different. You've been warned!
     *
     * @param   string $environment
     * @return  $this
     */
    public function setEnvironment($environment)
    {
        $this->config->setEnvironment($environment);
        return $this;
    }

    /**
     * Returns the environment that the config is using.
     *
     * @return  string|null
     */
    public function getEnvironment()
    {
        return $this->config->getEnvironment();
    }

    /**
     * Adds a path to look in for config files. This should be the top level directory as this class
     * will explore downwards for environment specific config.
     *
     * @param   string  $path   Path to the config files
     * @return  $this
     * @throws  Exception
     */
    public function addConfigPath($path)
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
    public function getConfigPaths()
    {
        return $this->configPaths;
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

        return $this->config->get($key, $default);
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
                $files[] = [
                    'path' => realpath($configFileCandidate),
                    'environment' => null
                ];
            }
        }

        // Second loop is finding 'environment level' config for each basepath:
        if ($this->config->getEnvironment() !== null) {
            foreach ($this->configPaths as $basePath) {
                $envConfigFileCandidate = $basePath.'/'.$this->config->getEnvironment().'/'.$namespace.'.php';
                if (file_exists($envConfigFileCandidate)) {
                    $files[] = [
                        'path' => realpath($envConfigFileCandidate),
                        'environment' => $this->config->getEnvironment()
                    ];
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
        $this->values[$file] = true;

        foreach ($requiredFiles as $fileToRequire) {
            $overrideConfig = require $fileToRequire['path'];
            $this->config->addConfig([$file => $overrideConfig], $fileToRequire['environment']);
        }
    }
}
