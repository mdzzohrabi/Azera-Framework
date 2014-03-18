<?php
namespace Azera\Database\Model;

class StaticModel
{

	protected static $model 	= null;

	public static function setModel( &$model )
	{
		static::$model 	= $model;
	}
	
	public static function __callStatic( $method , $args )
	{
		return call_user_func_array( array( static::$model , $method ) , $args );
	}

}


?>