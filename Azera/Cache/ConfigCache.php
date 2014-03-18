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

	public function get( $key , $value )
	{
		if ( $out = $this->read($key) )
			return $out;

		$value 	= $value();

		$this->write( $key , $value );

		return $value;
	}

	public function write( $key , $value , $callback = null )
	{
		Cache::write( $key , $value , $this->config , $callback );
		return $this;
	}

	public function old( $key , $file )
	{
		$path 	= self::path($key);
		
		if ( file_exists($path) AND file_exists($file) )
			return filemtime( $path ) < filemtime( $file );

		return true;
	}

	public function &delete( $key )
	{
		Cache::delete( $key , $this->config );
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