<?php
namespace Azera\Core;

class Config
{
	
	private static $configs 	= array();

	static function get( $key )
	{
		$configs 	= self::$configs;
		return eval( eas('configs' , $key) );
	}

	static function set( $key , $value )
	{
		eval('self::$configs["' . str_replace('.','"]["' , $key) . '"] = $value;');
	}

}
?>