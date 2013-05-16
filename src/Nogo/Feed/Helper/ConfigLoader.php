<?php
namespace Nogo\Feed\Helper;

use Aura\Sql\Exception;
use Symfony\Component\Yaml\Yaml;


class ConfigLoader implements \ArrayAccess
{
    protected $config = array();

    public function __construct($file)
    {
        $this->load($file);
    }

    public function load($file)
    {
        if (file_exists($file)) {
            $this->config =  Yaml::parse($file);
            $this->pathConvert($this->config);
        } else {
            throw new Exception('File [' . $file . '] not found.');
        }
    }

    public function mergeLoad($file, $first = false)
    {
        if (file_exists($file)) {
            $this->merge(Yaml::parse($file), $first);
        } else {
            throw new Exception('File [' . $file . '] not found.');
        }
    }

    /**
     * Merge array into config array.
     *
     * @param array $config array to merge
     * @param bool $first true, this config will be first, parameter array will be second
     */
    public function merge(array $config, $first = false)
    {
        if ($first) {
            $this->config = array_merge($this->config, $config);
        } else {
            $this->config = array_merge($config, $this->config);
        }
        $this->pathConvert($this->config);
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

    protected function pathConvert()
    {
        array_walk_recursive($this->config, function(&$item, $key) {
            if (preg_match('/%(.*_dir)%/', $item, $matches)) {
                $constName = strtoupper($matches[1]);
                if (defined($constName)) {
                    $value = constant($constName);
                    $item = str_replace('%' . $matches[1] . '%',$value, $item);
                } else if (array_key_exists($matches[1], $this->config)) {
                    $item = str_replace('%' . $matches[1] . '%', $this->config[$matches[1]], $item);
                }
            }
        });
    }
}