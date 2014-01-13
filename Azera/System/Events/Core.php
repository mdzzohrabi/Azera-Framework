<?php
namespace Azera\System\Events;

use Azera\Events\Handler;

class Core extends Handler
{
	
	function Start( $event , $args = null )
	{
		return strtr($args,array(
				'Hello'	=> 'Goodby'
			));
	}

	function End( $event , $args = null )
	{

	}

}
?>