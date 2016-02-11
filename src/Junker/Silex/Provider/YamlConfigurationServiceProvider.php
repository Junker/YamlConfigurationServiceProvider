<?php

namespace Junker\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;

use Junker\Silex\YamlFileLoader;


class YamlConfigurationServiceProvider implements ServiceProviderInterface
{
	protected $cache_dir;
	protected $debug;
	protected $file;

	protected $configCacheFactory;


	public function __construct($file, $options = NULL)
	{
		if (is_array($options))
		{
			if (isset($options['cache_dir']))
				$this->cache_dir = $options['cache_dir'];

			if (isset($options['debug']))
				$this->debug = $options['debug'];
		}

		$this->file = $file;
	}

	public function register(Application $app)
	{
		$app['config'] = $app->share(function () 
		{
			if ($this->cache_dir)
			{
				$cache = $this->getConfigCacheFactory()->cache($this->cache_dir.'/config.cache.php',
					function (ConfigCacheInterface $cache)
					{
						$config = $this->loadConfig();

						$content = sprintf('<?php use Junker\Silex\Config; $c = new Config(%s);', var_export($config->data, TRUE)) . PHP_EOL;
						$content .= 'return $c;';

						$cache->write($content, $config->getResources());
					}
				);

				$config = include $cache->getPath();
			}
			else
			{
				$config = $this->loadConfig();
			}

			return $config->data;
		});
	}


	public function boot(Application $app) 
	{
	}

	private function getConfigCacheFactory()
	{
		if ($this->configCacheFactory === NULL) 
		{
			$this->configCacheFactory = new ConfigCacheFactory($this->debug);
		}

		return $this->configCacheFactory;
	}

	protected function loadConfig()
	{
		$loader = new YamlFileLoader(new FileLocator(dirname($this->file)));

		$config = $loader->load($this->file);

		return $config;
	}
}