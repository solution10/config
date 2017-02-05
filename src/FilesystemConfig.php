<?php

namespace Solution10\Config;

use Solution10\Config\Common\Environment;

/**
 * Class FilesystemConfig
 *
 * Super simple config loader/reader class. Supports inheritance across environments,
 * but not at lot else as the aim is to be wicked fast and light.
 *
 * @package     Solution10\Config
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class FilesystemConfig implements ConfigInterface
{
    use Environment;

    /**
     * @var     string[]  Paths to the config files
     */
    protected $configPaths = [];

    /**
     * @var     array   Cache for the loaded config
     */
    protected $values = [];

    /**
     * You can pass in an initial set of configuration paths here, or leave it blank.
     *
     * @param   array       $initialPaths
     */
    public function __construct(array $initialPaths = [])
    {
        $this->addConfigPaths($initialPaths);
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
    public function get(string $key, $default = null)
    {
        // Grab the parts:
        $keyparts = explode('.', $key);
        $file = $keyparts[0];
        if (!array_key_exists($file, $this->values)) {
            $this->loadFile($file);
        }

        // If we still don't have the file value, return default
        if (!array_key_exists($file, $this->values) || $this->values[$file] === null) {
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
    public function getRequiredFiles(string $namespace): array
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
    protected function loadFile(string $file)
    {
        $requiredFiles = $this->getRequiredFiles($file);

        foreach ($requiredFiles as $fileToRequire) {
            $overrideConfig = require $fileToRequire;
            $this->values = array_replace_recursive($this->values, [$file => $overrideConfig]);
        }
    }
}
