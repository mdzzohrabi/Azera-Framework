<?php
namespace Azera\Events;

// import('Handler')->from('@Events');
// import('ObjectReflector')->from('@Util');

use Azera\Events\Handler;
use Azera\Util\ObjectReflector;

use Azera;
use Exception;

class Manager
{
	
	private static $handlers 	= array();

	/**
	 * Add Event Listener to listeners list
	 * @param 	string 				$eventName
	 * @param 	string|callable 	$handler
	 */
	public static function addEventListener( $eventName , $handler )
	{
		self::$handlers[ $eventName ][] 	= $handler;
	}

	public static function raiseEvent( $eventName , $args 	= null , $sender = null )
	{
		$temp			= explode( '.' , $eventName );
		$eventAction 	= array_pop( $temp );
		$eventNamespace	= implode( $temp , '.' );

		$files 			= Azera::scanDirectories( 'Events' , array(
				'module'	=> '*',
				'bundle'	=> '*',
				'pattern'	=> $eventNamespace . '.php'
			) );

		$event 	= (object)array(
				'sender'	=> &$sender,
				'name'		=> $eventName,
				'action'	=> $eventAction,
				'time'		=> microtime(true),
				'memory'	=> memory_get_usage(),
				'args'		=> $args
			);

		$result 	= $args;

		foreach ( $files as $file )
		{

			inc( $file );

			$class 	= strtr( $file , array(
					APP 	=> 'Bundle',
					Azera 	=> 'Azera',
					DS 		=> NS,
					'.php'	=> null,
					'.'		=> '_'
				) );

			if ( class_exists($class) )
				$handler 	= new $class;
			else
				throw new Exception( sprintf('Class "%s" not exists' , $class) );
			
			$object 	= new Azera\Util\ObjectReflector( $handler );

			if ( $object->hasMethod( $eventAction ) )
			{
				$result 	= $handler->{$eventAction}( $event , $args );
			}

		}

		if ( isset( self::$handlers[ $eventName ] ) )
		foreach ( self::$handlers[ $eventName ] as $listener )
		{
			if ( is_callable( $listener ) )
				$result 	= $listener( $event , $args );
		}

		return $result;

	}

}
?>