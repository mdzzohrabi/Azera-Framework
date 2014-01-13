<?php
namespace Azera\Routing;

use Azera\IO\Request;
use Azera\IO\Response;
use Azera\Util\String;
use Azera\Core\AreaManager;
use Azera\Routing\Router;
use Azera\Core\Process;

// init('@Routing.Router');

// use Azera\i19n\Locale;

class Dispatcher
{

	static function optimizeUrl( $url )
	{
		$url 	= trim( $url );
		if ( substr( $url , 0 , 1 ) != '/' ) $url 	= '/' . $url;
		if ( substr( $url , -1 , 1 ) == '/' ) $url 	= substr( $url , 0 , -1 );
		return $url;
	}
	
	static function dispatch( $url = null )
	{

		if ( is_null($url) ) $url 	= Request::uri();

		$url 	= self::optimizeUrl( $url );

		$passedArgs 	= array();
		$bundle 		= null;
		$module 		= null;
		$controller 	= null;
		$action 		= null;
		$args 			= array();
		
		//$lang 			= Locale::current('routePrefix');

		/**
		 * Find PassedArgs in Requested url
		 */
		preg_match_all('/'.'(\w+):([^\/]+)'.'/',$url,$passedArgs);

		if ( !empty( $passedArgs[0] ) )
		{

			foreach ( $passedArgs[0] as $a )
				$url 	= str_replace( '/' . $a , null, $url );

			$passedArgs 	= array_combine( $passedArgs[1] , $passedArgs[2] );
		}else
		{
			$passedArgs 	= array();
		}

		$parts 	= explode( '/' , $url );

		$area = AreaManager::findByPrefix( $parts[1] );

		if ( $area )
			$url 	=  self::optimizeUrl(str_replace( '/' . $area->routePrefix . '/' , '' , $url ));

		$found 	= false;

		foreach ( Router::routes( $area ? $area->name : null ) as $pattern => $route )
		{
			$route 	= (object)$route;

			/** Extract ":var"  matches **/
			preg_match_all('/:(\w+)/', $pattern , $vars );

			foreach ( $vars[0] as $var )
			{
				$pattern 	= str_replace( '/' . $var , null , $pattern);
			}

			$pattern 	.= '/';

			$fix 		 = !(in_array( 'args', $vars[1]) || in_array( 'action',$vars[1]));

			if ( ($fix && self::optimizeUrl($url) == self::optimizeUrl($pattern)) || (!$fix && substr( $url , 0 , strlen($pattern) ) == $pattern) )
			{

				$found 	= true;

				$after 	= explode('/' , substr( self::optimizeUrl( substr( $url , strlen($pattern) ) ) , 1 ) );

				if ( in_array( ':action' , $vars[0] ) )
				{
					$action 	= $after[0];
					unset( $after[0] );
				}else
				{
					$action 	= $route->action;
				}

				if ( in_array( ':args' , $vars[0] ) )
					$args 	= array_merge( $route->args , $after );
				else
					$args 	= $route->args;

				break;
			}

		}

		$passedArgs 	= array_merge( (array)$route->passedArgs , (array)$passedArgs );

		if ( !$found )
		{
			$route 		= null;
		}


		return compact('url','route', 'action' ,'args' , 'passedArgs','area');

	}

	public static function execute( $path )
	{

		$dispatch 	= (object)$path;

		$areaClass 	= $dispatch->area['class'];

		$area 	= new $areaClass( null , $dispatch );

		Process::area( $area );

		Response::write( $area->render() );

	}

}
?>