<?php
namespace Azera\Core;

use Azera;
use Azera\Util\Set;
use Azera\Routing\Dispatcher;

class AreaManager
{
	
	private static $areas 	 = array();

	/**
	 * Initialize Areas
	 */
	public static function init()
	{
		$areasFile 	= Azera::scanDirectories('Area');

		foreach ( $areasFile as $file )
			include_once $file;

	}

	public static function get( $area )
	{
		if ( isset( self::$areas[ $area ] ) )
			return self::$areas[$area];
		return false;
	}

	public static function findByPrefix( $uri )
	{
		$default 	= null;
		
		foreach ( self::$areas as $key => $value)
		{
			if ( $value['routePrefix'] == substr( $uri , 0 , strlen($value['routePrefix']) ) )
				return (object)$value;
			if ( $value['routePrefix'] == '' )
				$default = $value;
		}

		if ( $default )
			return (object)$default;

		return null;
	}

	public static function areas()
	{
		return self::$areas;
	}

	public static function add( $area )
	{
		$area 		= (object)$area;

		$area->routePrefix 	= Dispatcher::optimizeUri( $area->routePrefix );

		self::$areas[ $area->name ] 	= Set::extend(array(
				'name'			=> null,
				'routePrefix'	=> null,
				'title'			=> null
			), (array)$area );
	}

}
?>