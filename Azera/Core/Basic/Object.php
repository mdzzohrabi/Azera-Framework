<?php
namespace Azera\Core\Basic;

trait Object
{
	
	/*function toString()
	{
		return get_class($this);
	}*/

	static function toString()
	{
		return get_called_class();
	}

}
?>