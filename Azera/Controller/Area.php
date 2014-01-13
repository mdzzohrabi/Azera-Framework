<?php
namespace Azera\Controller;

use Azera\Core\Config;

class Area extends Controller
{
	
	public 	$controllerType 		= Types::Area;			// Controller Type

	public 	$title 					= null;					// Document Title

	public 	$tags 					= array();				// Page Tags

	public 	$theme 					= array();				// Theme
	
	public  $dispatch 				= null;

	public 	$view 					= 'Layout.default';

	public function __construct( $owner , $path )
	{
		$this->dispatch 	= $path;
		$this->theme 		= Config::get('Theme');
		parent::__construct( $owner );
	} 

}
?>