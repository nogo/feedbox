<?php
namespace Nogo\Feedbox\Api;


abstract class AbstractApi
{
    /**
     * @var array
     */
    private $readable = array();

    /**
     * @var array
     */
    private $writable = array();

    /**
     * Api definition
     *
     * @return array
     */
    abstract public function definition();

    /**
     * Array of readable fields defined by api
     * @return array
     */
    public function readableFields()
    {
        if (empty($this->readable)) {
            $api = $this->definition();
            $this->readable = [];
            foreach ($api as $key => $param) {
                if (isset($param['read']) && $param['read']) {
                    $name = $key;
                    if (array_key_exists('name', $param)) {
                        if (!empty($param['name'])) {
                            $name = $param['name'];
                        }
                    }
                    $this->readable[$key] = $name;
                }
            }
        }

        return $this->readable;
    }

    /**
     * Array of writable fields defined by api
     * @return array
     */
    public function writableFields()
    {
        if (empty($this->writable)) {
            $api = $this->definition();
            $this->writable = [];
            foreach ($api as $key => $param) {
                if (isset($param['write']) && $param['write']) {
                    $name = $key;
                    if (array_key_exists('name', $param)) {
                        if (!empty($param['name'])) {
                            $name = $param['name'];
                        }
                    }
                    $this->writable[$key] = $name;
                }
            }
        }

        return $this->writable;
    }

    /**
     * Deserialize data array with api definition
     *
     * @param array $data [ name => value ]
     * @param array $api [ key => name ]
     * @param array $result
     * @return array [ key => value ]
     */
    public function deserializeData(array $data, array $api = [], array $result = [])
    {
        if (empty($api)) {
            $api = $this->writableFields();
        }

        foreach ($api as $key => $name) {
            if (array_key_exists($name, $data)) {
                if (array_key_exists($key, $result)) {
                    if ($data[$name] != $result[$key]) {
                        $result[$key] = $data[$name];
                    }
                } else {
                    $result[$key] = $data[$name];
                }
            }
        }
        return $result;
    }

    /**
     * Serialize data array with api definition
     *
     * @param array $data [ key => value ]
     * @param array $api [ key => name ]
     * @param array $result
     * @return array [ name => value ]
     */
    public function serializeData(array $data, array $api = [], array $result = [])
    {
        if (empty($api)) {
            $api = $this->readableFields();
        }

        foreach ($api as $key => $name) {
            if (array_key_exists($key, $data)) {
                $result[$name] = $data[$key];
            }
        }
        return $result;
    }
}