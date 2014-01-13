<?php
namespace Azera\Debug\Exceptions;

use Azera\Util\String;
use Azera;

class ModelClassNotFound extends NotFound
{
	
	function __construct( $path )
	{
		$ns 	= String::className( $path );
		$file 	= Azera::dispatchFile( $path );
		parent::__construct("Model Class Not Found '$ns' in file '$file'");
	}

}
?>