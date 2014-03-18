<?php
Azera::alias([

	 	'String' 		=> 'Azera\Util\String',
	 	'Session'		=> 'Azera\Util\Session',
		'Set'			=> 'Azera\Util\Set',
		'Cookie'		=> 'Azera\Cache\Cookie',
	 	
	 	'Html'			=> 'Azera\View\Helper\Html',

	 	'Request'		=> 'Azera\IO\Request',
	 	'Response'		=> 'Azera\IO\Response',
		
		'View'			=> 'Azera\View\View',
		'Controller'	=> 'Azera\Controller\Controller',
		'Model'			=> 'Azera\Database\Model',
		
		'Route'			=> 'Azera\Routing\Router',

		'Config'		=> 'Azera\Core\Config',
		'Process'		=> 'Azera\Core\Process',

		'DB'			=> 'Azera\Database\Database',

		'Load'			=> 'Azera\Core\Loader'

	]);
?>