<?php
namespace Azera\Core;

class StartupManager
{
	
	private $startups 	= null;

	function __construct( $startups = array() )
	{
		$this->startups 	= $startups;
	}

	function execute()
	{
		foreach ( $this->startups as $startup )
			$startup->start();
	}

}
?>