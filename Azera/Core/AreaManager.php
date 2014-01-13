<?php
namespace Azera\Core;

use Azera;
use Azera\Util\Set;

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
			inc($file);

	}

	public static function get( $area )
	{
		if ( isset( self::$areas[ $area ] ) )
			return self::$areas[$area];
		return false;
	}

	public static function findByPrefix( $prefix )
	{
		$default 	= null;
		foreach ( self::$areas as $key => $value) {
			if ( $value['routePrefix'] == $prefix )
				return $value;
			if ( $value['routePrefix'] == '' )
				$default = $value;
		}
		if ( $default )
			return $default;
		return null;
	}

	public static function areas()
	{
		return self::$areas;
	}

	public static function add( $area )
	{
		$area 		= (object)$area;
		self::$areas[ $area->name ] 	= Set::extend(array(
				'name'			=> null,
				'routePrefix'	=> null,
				'title'			=> null
			), (array)$area );
	}

}
?>