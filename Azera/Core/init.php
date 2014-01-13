<?php
defined('CORE') or define('CORE' , __DIR__);
defined('LIB')  or define('LIB' , dirname(__DIR__));

require_once CORE . DS . 'Defines.php';
require_once CORE . DS . 'Functions.php';
require_once LIB  . DS . 'Debug' . DS . 'ErrorHandler.php';

//set_error_handler( 'Azera\Debug\ErrorHandler::handle' );

//init('@Core','@Cache','@Events','@Routing','@IO','@Util','@Bundle');
init('@Core');
?>