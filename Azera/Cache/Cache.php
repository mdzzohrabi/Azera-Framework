<?php
namespace Azera\Cache;

use Azera\Core\Config;
use Azera\IO\File;
use Azera\Util\Set;

class Cache
{

	private static $configs  = array(
			'default'	=> array(
					'fileName'	=> 'trim',
					'set'		=> 'serialize',
					'get'		=> 'unserialize',
					'path'		=> CACHE
				)
		);

	static function config( $config , $settings = array() )
	{
		self::$configs[ $config ] = Set::extend( array(
				'fileName'	=> 'trim',
				'set'		=> 'serialize',
				'get'		=> 'unserialize',
				'path'		=> CACHE
			) , $settings );
	}
	
	static function write( $key , $value , $config = 'default' , $callback = null )
	{

		if ( !$config = self::$configs[$config] )
		{
			return false;
		}

		$fileName 	= $config['fileName']( $key );
		$path 		= $config['path'] . DS . $fileName;
		$data 		= $callback ? $callback($value) : is_callable($config['set']) ? $config['set']( $value ) : $value;

		return File::writeAll( $path , $data );

	}

	static function read( $key , $config = 'default' , $callback = null )
	{
		if ( !$config = self::$configs[$config] )
		{
			return false;
		}

		$fileName 	= $config['fileName']( $key );
		$path 		= $config['path'] . DS . $fileName;

		if ( !$data = File::readAll( $path ) ) return ( !is_callable($callback) ? $callback : false );

		$data 		= $callback ? $callback($data) : is_callable($config['get']) ? $config['get']( $data ) : $data;

		return $data;

	}

	static function exists( $key , $config = 'default' )
	{
		if ( !$config = self::$configs[$config] )
		{
			return false;
		}

		$fileName 	= $config['fileName']( $key );
		$path 		= $config['path'] . DS . $fileName;

		return file_exists( $path );
	}

	static function path( $key , $config = 'default' )
	{
		if ( !$config = self::$configs[$config] )
		{
			return false;
		}

		$fileName 	= $config['fileName']( $key );
		return $config['path'] . DS . $fileName;
	}

	static function size( $key , $config = 'default' )
	{
		if ( !$config = self::$configs[$config] )
		{
			return false;
		}

		$fileName 	= $config['fileName']( $key );
		$path 		= $config['path'] . DS . $fileName;

		if ( !$data = File::size( $path ) ) return false;

		return $data;

	}

	static function inThe( $config = 'default' )
	{
		return new ConfigCache( $config );
	}

}
?>