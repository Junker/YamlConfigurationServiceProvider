<?php
 
namespace Junker\Silex;

use Symfony\Component\Config\Resource\ResourceInterface;

 
class Config
{
	public $data;
	private $resources = array();

	public function __construct(Array $data)
	{
		$this->data = $data;
	}

	public function addResource(ResourceInterface $resource)
	{
		$this->resources[] = $resource;
	}

	public function getResources()
	{
		return array_unique($this->resources);
	}

	public function addConfig(Config $config)
	{
		$this->data = array_replace_recursive($this->data, $config->data);

		$this->resources = array_merge($this->resources, $config->getResources());
	}
}