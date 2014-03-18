<?php
// Configuration
Config::set('Routing',[
		
		'Default'	=> [
			'Area'		=> 'Home',
			'Filters'	=> [ 'method' ]
		],

		'CaseSensitive'	=> false

	]);

// Filter routes by method
Route::filter('method',function( &$route ){

	if ( $route->acceptMethods )
	{
		return in_array( Request::method() , $route->acceptMethods );
	}

	return true;

});

/** System Core Public Web Files **/
Route::addStatic( 'system' , dirname(__DIR__) . DS . 'web' . DS . '*' );

/** TestKit Routing **/
Route::addForward( 'test' , Base . DS . 'Tests' );
Route::addStatic( 'testkit' , Azera . DS . 'TestKit' . DS . 'www' . DS . '*' , 'testkit' );

/** Public Cached Directory **/
Route::addStatic('cached' , CACHE . DS . 'public' . DS . '*' , 'cached');

/** Debug Routes **/
if ( Config::read('Debug') == true )
{
	Route::addStatic(
		'system/debug' ,
		Azera . DS . 'Debug' . DS . 'View' . DS . WWW . DS . '*',
		'system.debug'
	);
}
?>