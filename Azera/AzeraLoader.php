<?php
namespace Azera;

defined('DS')    or define('DS', DIRECTORY_SEPARATOR );
defined('Azera') or define('Azera' , __DIR__ );

define('TIME_START' , microtime(true) );
define('MEMORY_START' , memory_get_usage() );

include_once Azera . DS . 'Core' . DS . 'init.php';

/**
 *	Class autoloader
 */
spl_autoload_register(function( $class ){

	//$class 	= ( substr($class,0,strlen('Azera\\')) == 'Azera\\' ? '@' . substr($class,strlen('Azera\\')) : $class );

	$file 	= str_replace( NS , DS , $class ) . '.php';

    // Azera Root Folder
	if ( substr( $file , 0 , 5 ) == 'Azera' )
		$file 	= Azera . DS . substr( $file , 6 );
    // Application Root Folder
	elseif ( substr( $file , 0 , 3 ) == 'App' )
	{
		$file 	= APP . DS . substr( $file , 4 );
	}else
	{
		// Bundle\Azera\...
		$file 	= APP . DS . $file;
	}

	if ( file_exists( $file ) )
	{
		include_once $file;
	}

});

init('@Debug.Exceptions.*');
?>