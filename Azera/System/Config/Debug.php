<?php

Config::set('Debug' , true);

Config::set('Error',[
	'handler'	=> 'Azera\Debug\ErrorHandler::handleError',
	'trace'		=> true,
	'code'		=> false,
	'level'		=> E_ALL & ~E_DEPRECATED
]);

/*Config::set('Exception',[
		'handler'		=> 'Azera\Debug\ErrorHandler::handleException',
		'trace'			=> true
	]);*/

?>