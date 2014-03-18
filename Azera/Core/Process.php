<?php
namespace Azera\Core;

class Process
{

	private static $area 	= null;
	
	static function area( &$area = null )
	{
		if ( $area )
			return self::$area 	= $area;
		return self::$area;
	}

}
?>