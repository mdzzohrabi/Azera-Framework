<?php
namespace Azera\Debug;

/**
 * Debug Profiler Class
 * This class 
 */
class Profiler
{
	
	public static $_profiles 	= array();

	static function profile( $name , $value = null )
	{

		$operand 	 = '=';

		if ( is_string($value) && (substr($value,0,1) == '+' || substr($value,0,1) == '-') )
		{
			$operand 	= substr($value,0,1) . '=';
			$value 		= substr($value,1);
		}

		return eval('return self::$_profiles[' . str_replace( '.' , '][' , $name ) . '] ' . $operand . ' ' . $value.';');

	}

	static function profiles()
	{
		return self::$_profiles;
	}

}
?>