<?php
namespace Azera\Core;

use Azera\Util\String;
use Azera;
use Azera\Debug\Exceptions;

class Loader
{
	
	/**
	 * Load Model
	 * @param string|array 	$path 		- Model Path
	 * @param bool 			$instance 	- Create Instance From Model
	 * @return object | string
	 */
	static function Model( $path , $instance = true )
	{
		$path 	= (object)String::dispatch( $path , 'Model' );

		$file 	= Azera::dispatchFile( $path );

		if ( !$file )
		{
			throw new Exceptions\NotFound( sprintf('Model file not found `%s`', $path ) );
		}

		$modelNS 	= String::className( $path );

		include_once $file;

		if ( class_exists( $modelNS ) )
		{
			if ( $instance )
				return new $modelNS();
			return $modelNS;
		}

		throw new Exceptions\ModelNotFound( $path );
	}

	/**
	 * Load Controller
	 * @param string 	$path
	 * @return object
	 */
	static function Controller( $path , $instance = false , $owner = null )
	{
		$path 	= (object)String::dispatch( $path , 'Controller' );

		$file 	= Azera::dispatchFile( $path );

		if ( !$file )
		{
			throw new Exceptions\ControllerNotFound( $path );
		}

		$NS 	= String::className( $path );

		include_once $file;

		if ( class_exists( $NS ) )
		{
			if ( $instance )
				return new $NS( $owner );
			
			return $NS;
		}

		throw new Exceptions\ControllerClassNotFound( $path );
	}

}
?>