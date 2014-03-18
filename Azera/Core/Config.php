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

	static function read( $key )
	{
		$configs 	= self::$configs;
		return eval( eas('configs' , $key) );		
	}

	static function set( $key , $value )
	{
		eval('self::$configs["' . str_replace('.','"]["' , $key) . '"] = $value;');
	}

	static function write( $key , $value )
	{
		eval('self::$configs["' . str_replace('.','"]["' , $key) . '"] = $value;');
	}

	static function append( $key , $value )
	{
		eval( 'self::$configs["' . str_replace('.','"]["',$key) . '"] += (array)$value;' );
	}

	static function has( $key )
	{
		$var 	= 'self::$configs["' . str_replace('.','"]["',$key) . '"]';
		return eval("return isset($var) AND !empty($var);");
	}

}
?>