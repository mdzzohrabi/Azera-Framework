<?php
namespace Azera\Debug;

use Azera\Core\Config as Configure;

class Debug
{
	
	static function init()
	{

		// Error Handling
		if ( Configure::has('Error.handler') )
		{
			set_error_handler( Configure::read('Error.handler') );
		}

		// Exception Handling
		if ( Configure::has('Exception.handler') )
		{
			set_exception_handler( Configure::read('Exception.handler') );
		}

	}

}
?>