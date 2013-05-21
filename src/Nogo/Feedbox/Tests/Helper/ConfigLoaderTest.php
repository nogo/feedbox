<?php

namespace Nogo\Feedbox\Tests\Helper;

use Nogo\Feedbox\Helper\ConfigLoader;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $configLoader = new ConfigLoader();

        $this->assertNotNull($configLoader);
        $this->assertEmpty($configLoader->getConfig());

        $configLoader->load(dirname(__FILE__) . '/default.yml');
        $config = $configLoader->getConfig();
        $this->assertArrayHasKey('mode', $config);
    }

    public function testMerge()
    {
        $configLoader = new ConfigLoader();

        $this->assertNotNull($configLoader);
        $this->assertEmpty($configLoader->getConfig());

        $configLoader->load(dirname(__FILE__) . '/default.yml');
        $config = $configLoader->getConfig();
        $this->assertArrayHasKey('mode', $config);
        $this->assertEquals($config['mode'], 'dev');

        $configLoader->merge(array('mode' => 'prod'));
        $config = $configLoader->getConfig();
        $this->assertEquals($config['mode'], 'prod');
    }

    public function testConstrutorLoading()
    {
        $configLoader = new ConfigLoader(
            dirname(__FILE__) . '/default.yml'
        );

        $config = $configLoader->getConfig();

        $this->assertNotNull($configLoader);
        $this->assertArrayHasKey('mode', $config);
        $this->assertArrayHasKey('data_dir', $config);
        $this->assertTrue(strstr($config['data_dir'], 'feedbox') !== false);
    }

    public function testConstrutorMergedLoading()
    {
        $configLoader = new ConfigLoader(
            dirname(__FILE__) . '/default.yml',
            dirname(__FILE__) . '/config.yml'
        );

        $config = $configLoader->getConfig();

        $this->assertNotNull($configLoader);
        $this->assertArrayHasKey('mode', $config);
        $this->assertArrayHasKey('data_dir', $config);
        $this->assertTrue(strstr($config['data_dir'], 'data_test') !== false);
    }

}