<?php
use Azera\Cache\Cache;

Cache::config('view',array(
	'path'		=> CACHE . DS . 'view',
	'fileName'		=> function( $name )
	{
		return md5( $name ) . '.php';
	},
	'set'		=> null,
	'get'		=> null
));

Cache::config('public',array(
		'path'		=> CACHE . DS . 'public',
		'fileName'	=> 'trim',
		'set'		=> null,
		'get'		=> null
	));
?>