<?php
namespace Azera\Bundle;

class Bundle
{
	
	/**
	 * Bundle name
	 * @var string
	 */
	public $name 		= null;

	/**
	 * Bundle Modules Collection
	 * @var ModuleCollection
	 */
	public $modules 	= null;

	function __construct( $bundle = null , $modules = array() )
	{

		if ( $bundle )
		{

		}

	}

	function __toString()
	{
		return $this->title;
	}

}
?>