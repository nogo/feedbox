<?php
namespace Nogo\Feedbox\Helper;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigLoader
 *
 * @package Nogo\Feedbox\Helper
 */
class ConfigLoader implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $configFiles = array();
    /**
     * @var array
     */
    protected $config = array();

    /**
     * Constructor
     *
     * @param *args files to load
     */
    public function __construct()
    {
        if (func_num_args() > 0) {
            foreach (func_get_args() as $file) {
                try {
                    $this->load($file);
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * Load file into config
     *
     * @param $file
     * @throws \Exception
     */
    public function load($file)
    {
        if (file_exists($file)) {
            $hash = md5($file);
            if (!isset($this->configFiles[$hash])) {
                $this->configFiles[$hash] = $file;

                try {
                    $values = Yaml::parse($file);
                    $this->merge($values);
                } catch (ParseException $e) {
                }
            }
        } else {
            throw new \Exception('File [' . $file . '] not found.');
        }
    }

    /**
     * Merge array into config array.
     *
     * @param array $config array to merge
     * @param bool $first true, this config will be first, parameter array will be second
     */
    public function merge(array $config)
    {
        $this->config = array_merge($this->config, $config);
        $this->pathConvert();
    }

    /**
     * Convert "%.*_dir%" into real path
     */
    protected function pathConvert()
    {
        array_walk_recursive(
            $this->config,
            function (&$item, $key) {
                if (preg_match('/%(.*_dir)%/', $item, $matches)) {
                    $constName = strtoupper($matches[1]);
                    if (defined($constName)) {
                        $value = constant($constName);
                        $item = str_replace('%' . $matches[1] . '%', $value, $item);
                    } else {
                        if (array_key_exists($matches[1], $this->config)) {
                            $item = str_replace('%' . $matches[1] . '%', $this->config[$matches[1]], $item);
                        }
                    }
                }
            }
        );
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
}