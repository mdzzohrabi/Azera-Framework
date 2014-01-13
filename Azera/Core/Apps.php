<?php
namespace Azera\Core;

use Azera\Util\Set;
/**
 * Azera Applications Manager Class
 * @author Masoud Zohrabi ( @mdzzohrabi )
 */
class Apps
{

	private static $apps  	= array();

	/** 
	 * Add an App to System
	 * e.g add( "Azera.Acl" , "Azera.Mail" , "Azera.Forms" );
	 * e.g add( array("Azera.Acl","Azera.Users" , "Azera"	=> array( "Forms" ) ) );
	 */
	function add()
	{

		$args 	= func_get_args();

		foreach ($args as $arg)
		{
			if ( is_array( $arg ) )
			{
				foreach ( $arg as $a => $b )
				{
					if ( is_array( $b ) )
						foreach ( $b as $m )
							self::$apps[] = "{$a}.{$m}";
					else
						if ( is_string( $a ) )
							self::$apps[] 	= "{$a}.{$b}";
						else
							self::$apps[] 	= $b;
				}
			}else
			{
				self::$apps[] 	= $arg;
			}
		}

		return self::$apps;

	}

	/**
	 * List of active Applications
	 * @return array
	 */
	function getAll()
	{
		return self::$apps;
	}

	function app( $app , $array = false )
	{
		list( $bundle , $module ) 	= explode( '.' , $app );

		$appJson 	= ( $bundle == 'System' ? System : App . DS . str_replace( '.' , DS , $app ) ) . DS . 'app.json';

		if ( file_exists($appJson) )
			return json_decode( file_get_contents( $appJson ) , $array );

		return false;

	}

	function appAll( $node = null )
	{
		$result 	= array();

		$apps 		= self::getAll();

		foreach ( $apps as $app )
		{
			$json 	= self::app( $app , true );
			$result[] 	= $json;
		}

		if ( $node )
		return Set::extract( $result , $node );

		return $result;

	}


}
?>