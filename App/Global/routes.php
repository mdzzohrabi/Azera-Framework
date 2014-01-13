<?php
use Azera\Routing\Router;

Router::connect('/masoud/:action/:args',array(
	'route'		=> 'App\Controller\Test',
	'action'	=> 'index',
	'area'		=> 'Home'
));
?>