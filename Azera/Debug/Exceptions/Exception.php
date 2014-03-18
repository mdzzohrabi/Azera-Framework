<?php
namespace Azera\Debug\Exceptions;

use RuntimeException as PHPException;


class Exception extends PHPException
{
	
	protected 	$message 	= null;

	protected 	$code 		= 500;


	function __construct( $message = null , $code = null )
	{

		if ( is_array( $message ) )
		{
			$message 	=	vsprintf( $this->message , $message );
		}elseif ( empty($message) )
		{
			$message 	= $this->message;
		}

		if ( empty($code) )
			$code 	 =	$this->code;

		header("HTTP/1.1 $code",true,$code);

		parent::__construct( $message , $code );

	}

}
?>