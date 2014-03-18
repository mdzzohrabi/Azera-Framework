<?php
namespace Azera\Controller;

use Azera\Core\Config;
use Azera\Core\Process;
use Azera\Core\AreaManager;

class Area extends Controller
{

	static 	$routePrefix 			= null;

	static 	$areaName 				= null;

	public 	$controllerType 		= Types::Area;			// Controller Type

	public 	$title 					= null;					// Document Title

	public 	$tags 					= array();				// Page Tags

	public 	$theme 					= 'default';			// Theme
	
	public  $dispatch 				= null;

	public 	$view 					= 'Layout.default';

	public function __construct( $owner , $dispatch )
	{

		// set process aria to current area
		Process::area( $this );

		$this->dispatch 	= $dispatch;
		$this->theme 		= Config::read('Theme');
		parent::__construct( $owner );
	} 

	public function setTitle( $title )
	{
		$this->title 	= $title;
	}

	static function bindToSystem()
	{
		AreaManager::add([
				'name'			=> static::$areaName,
				'routePrefix'	=> static::$routePrefix,
				'class'			=> get_called_class()
			]);
	}

}
?>