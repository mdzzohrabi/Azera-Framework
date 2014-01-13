<?php
namespace Azera\Debug;

use Azera\View\View;
use Azera\IO\Response;
use Azera\Routing;

class Toolbar
{

	public $appPath 	= __DIR__;

	public $view 		= 'Debug.Toolbar';

	static function draw()
	{
		new static();
	}

	public function __construct()
	{

		$vars	=	array();

		$vars['staticRoutes']	= count(Routing\Router::statics());

		$View 	= new View( $this , $this->view , $vars );

		Response::write( $View->render() );

	}
	
}
?>