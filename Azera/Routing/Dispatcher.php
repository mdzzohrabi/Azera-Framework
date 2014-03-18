<?php
namespace Azera\Routing;

use Closure;
use Azera\IO\Request;
use Azera\IO\Response;
use Azera\Util\String;
use Azera\Core\AreaManager;
use Azera\Routing\Router;
use Azera\Core\Process;
use Azera\Controller;

// init('@Routing.Router');

// use Azera\i19n\Locale;

class Dispatcher
{

	const 	DELIMETER 	= '/';

	static function optimizeUri( $url )
	{
		$url 	= trim( $url );
		if ( substr( $url , 0 , 1 ) != '/' ) $url 	= '/' . $url;
		if ( substr( $url , -1 , 1 ) == '/' ) $url 	= substr( $url , 0 , -1 );
		return $url;
	}
	
	static function dispatch( $uri = null )
	{

		if ( is_null($uri) ) $uri 	= Request::uri();

		// Get Filtered and Sorted Routes
		$uri 		= self::optimizeUri( $uri );

		// Get filters
		$filters 	= Router::filters();

		$passedArgs 	= array();
		$bundle 		= null;
		$module 		= null;
		$controller 	= null;
		$action 		= null;
		$args 			= array();
		
		//$lang 			= Locale::current('routePrefix');

		// Find PassedArgs in Requested url
		preg_match_all('/(?P<key>[^\/]+):(?P<value>[^\/]+)/',$uri,$passedArgs);

		if ( !empty( $passedArgs[0] ) )
		{

			foreach ( $passedArgs[0] as $a )
				$uri 	= str_replace( '/' . $a , null, $uri );

			$passedArgs 	= array_combine( $passedArgs['key'] , $passedArgs['value'] );

		}else
		{
			$passedArgs 	= array();
		}

		// Take apart url by / delimeter
		$routeParts 	= explode( self::DELIMETER , $uri );

		// Find url url by prefix
		$area 	= AreaManager::findByPrefix( $uri );

		// Remove area prefix from url
		if ( $area )
		{
			$uri 	=  self::optimizeUri(str_replace( '/' . $area->routePrefix . '/' , null , $uri ));
		}

		$found 	= false;

		$routeFilter 	= [];

		if ( $area )
			$routeFilter 	+= [ 'area'	=> $area->name ];

		foreach ( Router::routes( $routeFilter ) as $pattern => $route )
		{
			if ( ($parsed	= $route->filter( $uri )) !== FALSE )
			{
				$found 	= true;
				$route 	= (object)$route->route();
				break;
			}

		}


		if ( !$found )
		{
			$route 		= false;
		}
		else
		{
			$passedArgs 	= array_merge( (array)$route->passedArgs , (array)$passedArgs );
		}


		return compact('uri','route', 'parsed', 'passedArgs','area');

	}

	public static function execute( $path  , $return = false )
	{

		$dispatch 	= (object)$path;

		Request::$passedArgs 	=$dispatch->passedArgs;

		if ( $dispatch->area ):

			$areaClass 	= $dispatch->area->class;

			// Create instanceof area object
			$area 		= new $areaClass( null , $dispatch );

			$output 	= $area->render();

		else:

			$output 	= Dispatcher::loadRoute( $dispatch );

		endif;

		if ( $return )
		{
			return $output;
		}

		// send result to output buffer
		Response::write( $output );

	}

	/**
	 * Load and execute route
	 * @param 	Object 	$dispatch 	Dispatched route
	 * @param 	Object 	$owner 		Owner controller
	 * @return 	String
	 */

	public static function loadRoute( $dispatch , $owner = false )
	{

		$iteral 	= [ Route::ACTION , Route::CONTROLLER , Route::LANG , Route::ARGS ];

		$args 		= array_diff_key( $dispatch->parsed , array_flip($iteral) );

		$result		= null;

		if ( $dispatch->route->route instanceof Closure )
		{
			$result 	= call_user_func_array( $dispatch->route->route , $args);
		}

		return $result;

	}

}
?>