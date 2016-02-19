<?php

namespace Junker\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\FileLocator;
use Junker\Silex\YamlFileLoader;

class YamlConfigurationServiceProvider implements ServiceProviderInterface
{
    protected $cacheDirPath;
    protected $configFilePath;
    protected $debug;

    protected $configCacheFactory;

    public function __construct($configFilePath, $options = null)
    {
        $this->debug = $app['debug'];

        if (is_array($options)) {

            if (isset($options['cache_dir'])) {
                $this->cacheDirPath = $options['cache_dir'];
            }

            if (isset($options['debug'])) {
                $this->debug = $options['debug'];
            }

        }


        $this->configFilePath = $configFilePath;
    }

    public function register(Application $app)
    {
        $app['config'] = $app->share(function () {
            if ($this->cacheDirPath) {
                $cache = $this->getConfigCacheFactory($this->debug)->cache($this->cacheDirPath.'/config.cache.php',
                    function (ConfigCacheInterface $cache) {
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
        });
    }

    public function boot(Application $app)
    {
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
