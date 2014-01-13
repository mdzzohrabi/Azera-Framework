<?php
namespace Azera\Util;

use ReflectionClass;

class ObjectReflector extends ReflectionClass
{

	public static function reflect( $object )
	{
		return new self( $object );
	}

}
?>