<?php

use Silex\Application;
use Junker\Silex\Provider\YamlConfigurationServiceProvider;


class ConfigTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG_FILE = __DIR__ . '/../res/config.yml';
    const CACHE_PATH = '/tmp/cache_config_123634f3d';

    public function testConfig()
    {
        $app = new Application();

        $app->register(new YamlConfigurationServiceProvider(self::CONFIG_FILE));

        $this->assertEquals($app['config']['db']['pass'], '123123');
        $this->assertEquals($app['config']['facebook']['debug'], true);
        $this->assertEquals(count($app['config']['security']['rules']['IS_AUTHENTICATED_ANONYMOUSLY']), 2);
    }

    public function testCache()
    {
        $app = new Application();

        system("rm -rf " . escapeshellarg(self::CACHE_PATH));

        $app->register(new YamlConfigurationServiceProvider(self::CONFIG_FILE, ['cache_dir' => self::CACHE_PATH]));

        $this->assertEquals($app['config']['db']['pass'], '123123');

        $this->assertFileExists(self::CACHE_PATH . '/config.cache.php');
        $this->assertFileExists(self::CACHE_PATH . '/config.cache.php.meta');

        $app = new Application();

        $app->register(new YamlConfigurationServiceProvider(self::CONFIG_FILE, ['cache_dir' => self::CACHE_PATH]));

        $this->assertEquals($app['config']['db']['pass'], '123123');
    }

}
