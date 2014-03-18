<?php
namespace Azera\Debug\Exceptions;

use Azera\Util\String;
use Azera;

class ModelNotFound extends NotFound
{

	protected $message 	= '`%s` Model not found in `%s`';
	
	function __construct( $path )
	{
		$ns 	= String::className( $path );
		$file 	= Azera::dispatchFile( $path );
		parent::__construct( [ $ns , $file ] );
	}

}
?>