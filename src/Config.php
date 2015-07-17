<?php

namespace Solution10\Config;

/**
 * Class Config
 *
 * Super simple config loader/reader class. Supports inheritance across environments,
 * but not at lot else as the aim is to be wicked fast and light.
 *
 * @package Solution10\Config
 */
class Config
{
    /**
     * @var     string[]  Paths to the config files
     */
    protected $configPaths;

    /**
     * @var     string  Environment folder. Defaults to "production" which is the top-level.
     */
    protected $environment = "production";

    /**
     * @var     array   Cache for the loaded config
     */
    protected $loaded = [];

    /**
     * Pass in the path to the config files top-level directory (ie app/config) and optionally
     * an environment. Defaults to "production".
     *
     * @param   string|array    $path           Path (or an array of paths) to the base directories of the config files
     * @param   string          $environment    Name of the environment to load for (null for 'production')
     * @throws  Exception
     */
    public function __construct($path, $environment = null)
    {
        $environment = ($environment === null)? 'production' : $environment;

        $this->addBasePath($path);
        $this->setEnvironment($environment);
    }

    /**
     * Adds a path to look in for config files. This should be the top level directory as this class
     * will explore downwards for environment specific config.
     *
     * @param   string|array  $path   Path (or paths) to the config files
     * @return  $this
     * @throws  Exception
     */
    public function addBasePath($path)
    {
        $paths = (!is_array($path))? [$path] : $path;
        foreach ($paths as $p) {
            if (!file_exists($p) || !is_dir($p) || !is_readable($p)) {
                throw new Exception(
                    'Invalid or unreadable path: '.$p,
                    Exception::INVALID_PATH
                );
            }
            $this->configPaths[] = $p;
        }
        return $this;
    }

    /**
     * Returns only the first config path we're using. This is here for backwards compat only,
     * you should migrate to using basePaths() instead.
     *
     * @return  string|null
     * @deprecated
     */
    public function basePath()
    {
        return (array_key_exists(0, $this->configPaths))? $this->configPaths[0] : null;
    }

    /**
     * Returns all the config paths we're using
     *
     * @return  string[]
     */
    public function basePaths()
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
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Returns the environment that the config is using.
     *
     * @return  string
     */
    public function environment()
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
        if (!array_key_exists($file, $this->loaded)) {
            $this->loadFile($file);
        }

        // If we still don't have the file value, return default
        if ($this->loaded[$file] === null) {
            return $default;
        }

        // Now the tricky part, recursively read the path from the parts:
        $totalParts = count($keyparts);
        $i = 1;
        $value = $this->loaded;
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
    public function requiredFiles($namespace)
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
        $requiredFiles = $this->requiredFiles($file);
        $loadedConfig = [];

        foreach ($requiredFiles as $fileToRequire) {
            $overrideConfig = require $fileToRequire;

            // And recursively overwrite:
            $this->recursiveOverwrite($overrideConfig, $loadedConfig);
        }

        $this->loaded[$file] = (!empty($loadedConfig))? $loadedConfig : null;
    }

    /**
     * Recursively overwrites the values from $source into $dest
     *
     * @param   array   $source
     * @param   &array  $dest
     */
    protected function recursiveOverwrite($source, &$dest)
    {
        foreach ($source as $key => $value) {
            if (is_array($value) && array_key_exists($key, $dest)) {
                // We need to merge a sub-array:
                $this->recursiveOverwrite($value, $dest[$key]);
            } else {
                // Overwrite/insert the key
                $dest[$key] = $value;
            }
        }
    }
}
