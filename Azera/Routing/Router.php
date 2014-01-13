<?php
namespace Azera\Routing;

use Azera\Util\Set;

class Router
{

	private static $routes 	= array();					// Application Routes
	private static $static 	= array();					// Map From Public Url to Local Name
	private static $forward = array();					// Forwarded Routes
	private static $staticMaps 	= array(); 				// Map from Local Name to Public Name
	private static $staticFolder 	= array();

	/**
	 * Return Statics Files
	 * @return array
	 */
	static function statics()
	{
		return self::$static;
	}

	/**
	 * Optimize a URL
	 * e.g 	home/ 	=> /home
	 * @param 	string $url
	 * @return 	string
	 */
	static function optimzeUrl( $url )
	{
		$url 	= trim( $url );
		if ( substr( $url , 0 , 1 ) != '/' ) $url 	= '/' . $url;
		if ( substr( $url , -1 , 1 ) == '/' ) $url 	= substr( $url , 0 , -1 );
		return $url;
	}

	/**
	 * is a static file ?
	 * @param 	string $url
	 * @return 	bool
	 */
	static function hasStatic( $url )
	{
		return isset( self::$static[$url] ) ? self::$static[ $url ] : false;
	}

	/**
	 * Add Forward Prefix to a Local Path
	 * e.g /forum 	to /home/site/App/Scripts/Forum
	 * @param 	string 	$prefix
	 * @param 	string 	$path
	 */
	static function addForward( $prefix , $path )
	{
		self::$forward[ $prefix ] 	= $path;
	}

	static function findForwardFile( $url )
	{
		$url 	= self::optimzeUrl( $url );
		foreach (self::$forward as $prefix => $path)
		{
			$prefix 	= self::optimzeUrl( $prefix );
			if ( substr( $url , 0 , strlen( $prefix ) ) == $prefix )
				return $path . DS . substr( $url , strlen( $prefix ) + 1 );
		}
		return false;
	}

	static function routes( $area = null )
	{
		$routes 	= array();
		
		if ( $area )
		{
			foreach ( self::$routes as $route )
				if ( strtolower($route['area']) == strtolower($area) )
					$routes[] 	= $route;
		}else
		{
			$routes 	= self::$routes;
		}

		uksort( $routes , function( $a , $b ){
			return ( $a < $b );
		});
		return $routes;
	}

	/**
	 * Add Static Files to Router
	 * @param 	string 	$prefix
	 * @param 	string 	$path
	 * @param 	string 	$name
	 */
	static function addStatic( $prefix , $path , $name = null )
	{

		foreach ( glob( $path ) as $file )
		{
			$fileName 	=	end( explode( DS , $file ) );
			$route 	=  '/' . $prefix . '/' . $fileName;
			self::$static[ $route ] 	= $file;
			self::$staticMaps[ $name . '/' . $fileName ] = $route;
			self::$staticFolder[ $name ] 	= array(
					'prefix'	=> $prefix,
					'path'		=> $path
				);
		}

	}

	static function refreshStatic( $name = null )
	{
		if ( $name && $static = self::$staticFolder[ $name ] )
		{
			foreach ( glob( $static['path'] ) as $file )
			{
				$fileName 	=	end( explode( DS , $file ) );
				$route 	=  '/' . $static['prefix'] . '/' . $fileName;
				self::$static[ $route ] 	= $file;
				self::$staticMaps[ $name . '/' . $fileName ] = $route;
			}
		}
	}

	static function asset( $mapKey )
	{
		return self::$staticMaps[ $mapKey ];
	}
	
	static function connect( $pattern , $settings = array() )
	{

		self::$routes[ $pattern ] 	= Set::extend( array(
				'route'		=> null,				// ?
				'action' 	=> null,				// Default Action
				'args'		=> array(),				// Required Arguments
				'passedArgs'=> array(),				// Default PassedArgs
				'area'		=> null 				// Controller Route Area
			) , $settings );

	}

}
?>