<?php
use Azera\Routing\Router;
use Azera\Core\Config;

/** System Core Public Web Files **/
Router::addStatic( 'system' , dirname(__DIR__) . DS . 'web' . DS . '*' );

/** TestKit Routing **/
Router::addForward( 'test' , Base . DS . 'Tests' );
Router::addStatic( 'testkit' , Azera . DS . 'TestKit' . DS . 'www' . DS . '*' );

/** Public Cached Directory **/
Router::addStatic('cached' , CACHE . DS . 'public' . DS . '*' , 'cached');

/** Debug Routes **/
if ( Config::get('Debug') == true )
{
	Router::addStatic(
		'system/debug' ,
		Azera . DS . 'Debug' . DS . 'View' . DS . 'www' . DS . '*',
		'system.debug'
	);
}
?>