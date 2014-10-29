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
     * @var     string  Path to the config files
     */
    protected $configPath;

    /**
     * @var     string  Environment folder. Defaults to "production" which is the top-level.
     */
    protected $environment = "production";

    /**
     * @var     array   Cache for the loaded config
     */
    protected $loaded = array();

    /**
     * Pass in the path to the config files top-level directory (ie app/config) and optionally
     * an environment. Defaults to "production".
     *
     * @param   string  $path           Path to the config files
     * @param   string  $environment    Name of the environment to load for.
     * @throws  Exception
     */
    public function __construct($path, $environment = 'production')
    {
        $this->setBasePath($path);
        $this->setEnvironment($environment);
    }

    /**
     * Sets the path to the config files. Note that this invalidates all loaded
     * config so far, so if you've used values before changing this they might
     * now be different. You've been warned!
     *
     * @param   string  $path   Path to the config files
     * @return  $this
     * @throws  Exception
     */
    public function setBasePath($path)
    {
        if (!file_exists($path) || !is_dir($path) || !is_readable($path)) {
            throw new Exception(
                'Invalid or unreadable path: '.$path,
                Exception::INVALID_PATH
            );
        }

        $this->configPath = $path;
    }

    /**
     * Returns the config path we're using
     *
     * @return  string
     */
    public function basePath()
    {
        return $this->configPath;
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
     * @param   string  $key    Key path (see above)
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
     * Load a file, including the environment config if present. If the file
     * does not exist, this won't throw, it'll just quietly insert null into
     * the array, preventing another load attempt.
     *
     * @param   string  $file   File to load
     * @return  void
     */
    protected function loadFile($file)
    {
        $basePath = $this->basePath().'/'.$file.'.php';
        $overloadPath = $this->basePath().'/'.$this->environment.'/'.$file.'.php';

        $loadedConfig = array();

        // Load the base config:
        if (file_exists($basePath)) {
            $loadedConfig = require $basePath;
        }

        // Now check for overrides config:
        if ($this->environment() != 'production' && file_exists($overloadPath)) {
            $overrideConfig = require $overloadPath;

            // Now recursively overload:
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
