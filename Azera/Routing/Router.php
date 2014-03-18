<?php
namespace Azera\Routing;

use Azera\Util\Set;
use Azera\Util\String;

class Router
{

	private static $routes 			= [];					// Application Routes
	private static $static 			= [];					// Map From Public Url to Local Name
	private static $forward 		= [];					// Forwarded Routes
	private static $staticMaps 		= []; 				// Map from Local Name to Public Name
	private static $staticFolder 	= [];
	private static $filters 		= [];

	/**
	 * Add Filter to routing dispatcher
	 * @param 	string $filter
	 */
	function filter( $filterName , $callable = null )
	{
		
		if ( is_null($callable) )
			return self::$filters[$filterName];

		self::$filters[$filterName] 	= $callable;
	}

	/**
	 * Return Statics Files
	 * @return array
	 */
	static function statics()
	{
		return self::$static;
	}

	/**
	 * Return routing filters
	 */
	static function filters( $names = null )
	{
		if ( $names )
		{
			return array_intersect_key(self::$filters, $names);
		}

		return self::$filters;
	}

	/**
	 * Optimize a URL
	 * e.g 	home/ 	=> /home
	 * @param 	string $url
	 * @return 	string
	 */
	static function optimzeUri( $uri )
	{
		$uri 	= trim( $uri );
		if ( substr( $uri , 0 , 1 ) != '/' ) $uri 	= '/' . $uri;
		if ( substr( $uri , -1 , 1 ) == '/' ) $uri 	= substr( $uri , 0 , -1 );
		return $uri;
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
		$url 	= self::optimzeUri( $url );
		foreach (self::$forward as $prefix => $path)
		{
			$prefix 	= self::optimzeUri( $prefix );
			if ( substr( $url , 0 , strlen( $prefix ) ) == $prefix )
				return $path . DS . substr( $url , strlen( $prefix ) + 1 );
		}
		return false;
	}

	static function routes( $filters 	= array() )
	{
		$routes 	= array();
		
		if ( !empty($filters) )
		{
			foreach ( self::$routes as $pattern => $route )
			{
				if ( $route->has( $filters ) )
					$routes[ $pattern ] 	= $route;
			}
		}else
		{
			$routes 	= self::$routes;
		}

		uksort( $routes , function( $a , $b ){
			return (string)$a > (string)$b;
		});

		return $routes;
	}

	/**
	 * Add Static Files to Router
	 * @param 	string 	$prefix 	Client url prefix
	 * @param 	string 	$path 		Local Path
	 * @param 	string 	$name 		Namespace scope name
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
				$fileName 	=	String::toListArray( $file , DS )->last();
				$route 	=  '/' . $static['prefix'] . '/' . $fileName;
				self::$static[ $route ] 	= $file;
				self::$staticMaps[ $name . '/' . $fileName ] = $route;
			}
		}
	}

	static function localAsset( $mapKey )
	{
		return self::$static[ self::$staticMaps[ $mapKey ] ];
	}

	static function asset( $mapKey )
	{
		if ( isset( self::$staticMaps[ $mapKey ] ) )
			return BASE_URI . self::$staticMaps[ $mapKey ];

		return BASE_URI . '/' . $mapKey;

	}
	
	static function connect( $pattern , $settings = array() )
	{

		if ( !is_array($settings) )
		{
			$settings 	= [
				'route'	=> $settings
			];
		}

		// Add / to first of pattern
		if ( substr( $pattern , 0 , 1 ) != '/' )
		{
			$pattern 	= '/' . $pattern;
		}

		return self::$routes[ $pattern ] 	= new Route( $settings + [ 'pattern' => $pattern ] );

	}

	static function define( $name , $regexp )
	{
		Route::define($name,$regexp);
	}

	/**
	 * Return http based controller uri
	 * @param 	String 	$route
	 * @param 	Array 	$data
	 * @param 	Boolean $fullURI
	 * @return 	String
	 */
	static function url( $route, $data = [] , $fullURI = false )
	{



	}
}
?>