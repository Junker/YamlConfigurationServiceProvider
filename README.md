#YamlConfigurationServiceProvider
YAML Configuration Service Provider for Silex

[![Latest Stable Version](https://poser.pugx.org/junker/yaml-configuration-service-provider/v/stable)](https://packagist.org/packages/junker/yaml-configuration-service-provider)
[![Total Downloads](https://poser.pugx.org/junker/yaml-configuration-service-provider/downloads)](https://packagist.org/packages/junker/yaml-configuration-service-provider)
[![License](https://poser.pugx.org/junker/yaml-configuration-service-provider/license)](https://packagist.org/packages/junker/yaml-configuration-service-provider)

##Requirements
silex 1.x

##Installation
The best way to install YamlConfigurationServiceProvider is to use a [Composer](https://getcomposer.org/download):

    php composer.phar require junker/yaml-configuration-service-provider

##Supports
- Recursive config imports ([Configuration Organization](http://symfony.com/doc/current/cookbook/configuration/configuration_organization.html))
- Config Cache (Performance boost)

##Examples

```php
use Junker\Silex\Provider\YamlConfigurationServiceProvider;

$app->register(new YamlConfigurationServiceProvider('config.yml'));

#or

$app->register(new YamlConfigurationServiceProvider('config.yml', ['cache_dir' => '/tmp/config_cache', 'debug' => $app['debug']]));

$db_host = $app['config']['db']['host'];

```


config.yml 
```yaml
imports:
     - { resource: 'site/config.yml' }
     - { resource: 'security.yml' }

db:
    host: localhost
    login: root
    pass: 123123
    database: site12

facebook:
    scope: 'public_profile,email,user_birthday,user_location,user_photos'
    secret_key: FDSLKFDNSLsdre23lkndas
```

