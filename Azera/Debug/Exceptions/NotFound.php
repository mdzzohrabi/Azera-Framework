<?php
namespace Azera\Debug\Exceptions;

class NotFound extends Exception
{
	
	protected 	$message 	= '%s Not Found';

	protected 	$code 		= 404;

}
?>