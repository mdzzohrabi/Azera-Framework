<?php
/**
 * Azera Framework v3.1
 * Definations
 * @author Masoud Zohrabi (@mdzzohrabi)
 */

foreach ( array(
		'NS'	=> '\\',
		'NL'	=> PHP_EOL,
		'DS'	=> DIRECTORY_SEPARATOR,
        'BR'    => '<br/>',
        'System'=> Azera . DS . 'System',
        'FULL_BASE_URL'	=> ( $_SERVER['HTTPS'] ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'],
        'CACHE'	=> 'tmp',
        'Base'	=> dirname(dirname(__DIR__)),
        'Themes'=> APP . DS . 'Themes'
	) as $const => $value )
	defined( $const ) or define( $const , $value );
?>