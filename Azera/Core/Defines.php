<?php
/**
 * Azera Framework
 * Definations
 * @author Masoud Zohrabi (@mdzzohrabi)
 */


// Short
defined('NS')           or define('NS', '\\');
defined('NL')           or define('NL', PHP_EOL);
defined('DS')           or define('DS', DIRECTORY_SEPARATOR);
defined('BR')           or define('BR', '<br/>');

// Folder names
defined('TEMP')       	or define('TEMP' ,'tmp');
defined('VIEW')			or define('VIEW','View');
defined('SYSTEM')		or define('SYSTEM' , 'System');
defined('THEMES')		or define('THEMES' , 'Themes');
defined('WWW')			or define('WWW',	'www');

// Local paths
defined('System')       or define('System', Azera . DS . SYSTEM );
defined('Base')         or define('Base', dirname(dirname(__DIR__)));
defined('ROOT')         or define('ROOT', str_replace('/',DS,$_SERVER['DOCUMENT_ROOT']) );
defined('Themes')       or define('Themes', APP. DS . 'Themes');

// Web paths
defined('FULL_BASE_URL')        or define('FULL_BASE_URL', ( $_SERVER['HTTPS'] ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST']);
defined('BASE_URI')             or define('BASE_URI', str_replace(DS,'/',substr(Base,strlen(ROOT))) );
defined('URI')                  or define('URI', str_replace( BASE_URI , '' , $_SERVER['REQUEST_URI'] ) );


// Defines
defined('MESSAGE') 		or define('MESSAGE', 0b000 );
defined('ERROR') 		or define('ERROR', 0b001 );
defined('WARNING') 		or define('WARNING', 0b010 );
defined('SUCESS') 		or define('SUCESS', 0b011 );
?>