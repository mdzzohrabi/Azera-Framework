<?php
namespace Azera\Cache;

class ConfigCache
{
	
	public $config 	 = null;

	public function __construct( $config )
	{
		$this->config 	= $config;
	}

	public function read( $key , $default = null )
	{
		return Cache::read( $key , $this->config , $default );
	}

	public function write( $key , $value , $callback = null )
	{
		Cache::write( $key , $value , $this->config , $callback );
		return $this;
	}

	public function exists( $key )
	{
		return Cache::exists( $key , $this->config );
	}

	public function path( $key )
	{
		return Cache::path( $key , $this->config );
	}

}
?>