<?php

use Azera\Core\Config;
/**
 * Application Database Configuration
 */
Config::set('Database',array(
	'host'		=> 'localhost',
	'username'	=> 'root',
	'password'	=> '',
	'database'	=> 'azera_3',
	'engine'	=> 'MySQL'
));

Config::set('Theme','default');

Config::set('Debug', true);
?>