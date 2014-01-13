<?php
namespace Azera\Debug\Exceptions;

class ViewNotFound extends NotFound
{
	
	function __construct( $controller = null , $view = null , $scans = array() )
	{
		parent::__construct("View '$view' For '$controller' Not Found");
	}

}
?>