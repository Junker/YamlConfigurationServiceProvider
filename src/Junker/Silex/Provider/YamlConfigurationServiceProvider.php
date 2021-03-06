<?php

namespace Junker\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\FileLocator;
use Junker\Silex\YamlFileLoader;

class YamlConfigurationServiceProvider implements ServiceProviderInterface
{
    protected $cacheDirPath;
    protected $configFilePath;
    protected $configCacheFactory;

    public function __construct($configFilePath, $options = null)
    {
        if (is_array($options)) {
            if (isset($options['cache_dir'])) {
                $this->cacheDirPath = $options['cache_dir'];
            }
        }

        $this->configFilePath = $configFilePath;
    }

    public function register(Container $app)
    {
        $app['config'] = function($app) {
            if ($this->cacheDirPath) {
                $cache = $this->getConfigCacheFactory($app['debug'])->cache($this->cacheDirPath.'/config.cache.php',
                    function(ConfigCacheInterface $cache) {
                        $config = $this->loadConfig();

                        $content = sprintf('<?php use Junker\Silex\Config; $c = new Config(%s);', var_export($config->data, true)).PHP_EOL;
                        $content .= 'return $c;';

                        $cache->write($content, $config->getResources());
                    }
                );

                $config = include $cache->getPath();
            } else {
                $config = $this->loadConfig();
            }

            return $config->data;
        };
    }

    private function getConfigCacheFactory($debug = false)
    {
        if ($this->configCacheFactory === null) {
            $this->configCacheFactory = new ConfigCacheFactory($debug);
        }

        return $this->configCacheFactory;
    }

    protected function loadConfig()
    {
        $loader = new YamlFileLoader(new FileLocator(dirname($this->configFilePath)));

        $config = $loader->load($this->configFilePath);

        return $config;
    }
}
