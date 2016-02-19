<?php

namespace Junker\Silex;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

class YamlFileLoader extends FileLoader
{
    private $yamlParser;

    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $data = $this->loadFile($path);

        // empty file
        if ($data === null) {
            $data = array();
        }

        // not an array
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $file));
        }

        $config = new Config($data);
        $config->addResource(new FileResource($path));

        // imports
        $this->parseImports($config, $path);

        return $config;
    }

    protected function loadFile($file)
    {
        if (!class_exists('Symfony\Component\Yaml\Parser')) {
            throw new RuntimeException('Unable to load YAML config files as the Symfony Yaml Component is not installed.');
        }

        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
        }

        if ($this->yamlParser === null) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $data = $this->yamlParser->parse(file_get_contents($file));
        } catch (ParseException $e) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $file), 0, $e);
        }

        return $data;
    }

    private function parseImports(Config &$config, $file)
    {
        if (!isset($config->data['imports'])) {
            return;
        }
        if (!is_array($config->data['imports'])) {
            throw new InvalidArgumentException(sprintf('The "imports" key should contain an array in %s. Check your YAML syntax.', $file));
        }

        $defaultDirectory = dirname($file);

        foreach ($config->data['imports'] as $import) {
            if (!is_array($import)) {
                throw new InvalidArgumentException(sprintf('The values in the "imports" key should be arrays in %s. Check your YAML syntax.', $file));
            }

            $this->setCurrentDir($defaultDirectory);
            $sub_config = $this->import($import['resource'], null, isset($import['ignore_errors']) ? (bool) $import['ignore_errors'] : false, $file);

            $config->addConfig($sub_config);

            unset($config->data['imports']);
        }
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'yaml' === $type);
    }
}
