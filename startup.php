<?php
/**
 *	Azera Framework v3.1
 * 
 *	This is a PHP Framework to design easily and powerful
 *	applications based on PHP.
 * 
 *	Development by @mdzzohrabi
 *	@author		Masoud Zohrabi
 */
namespace Azera;

defined( 'DS' )		or define('DS'		, DIRECTORY_SEPARATOR);
defined('Base')		or define('Base'	, __DIR__);
defined('APP')		or define('APP'		, Base . DS . 'App');
defined('Azera')	or define('Azera'	, Base . DS . 'Azera');
defined('CACHE')	or define('CACHE'	, APP . DS . 'tmp');

require_once Azera . DS . 'AzeraLoader.php';

use Azera;

use Azera\IO\Request;
use Azera\IO\Response;
use Azera\Routing\Router;
use Azera\Routing\Dispatcher;
use Azera\Core\StartupManager;
use Azera\Core\Apps;
use Azera\Debug\Exceptions\Exception;
use Azera\Debug\Debug;
use Azera\Util\Session;

// Refuse direct script access
if ( Request::uri() == '/startup.php' )
{
    Response::send( 'Direct access denied' );
}

// Load All Config Files
Azera::loadAll('Config',[ 'reverse' => true ]);

// Error Handling and Development Enviroment initialize
Session::start();

Debug::init();

// Add System Core Bundle
Apps::add('System');

// Load Routing Configurations from *\Config\Routing.php
$routing = Azera::scanDirectories('Config',array(
		'pattern'	=> 'Routing.php'
	));

// include routing files
inc( $routing );

// Static routing controll
if ( $staticFile = Router::hasStatic( urldecode(Request::uri()) ) )
{
	Response::sendFile( $staticFile );
	Response::send();
}

// Forwarded Requests
if ( $file = Router::findForwardFile( Request::uri() ) )
{
	if ( file_exists($file) )
		include_once $file;
	else
		throw new Exception( sprintf("Request file not found '%s'",$file), 404);
		
	return;
}

// Load All Global Files
Azera::loadAll('Global');

// Startup Manager manage startup functions
$startupFiles 	= Azera::scanDirectories( 'Startup' , array(
		'bundle'		=> '*',
		'module'		=> '*'
	) );

$startups 	= array();

foreach ( $startupFiles as $file )
	$startups[] 	= include_once $file;

$startup 	= new StartupManager( $startups );

$startup->execute();

// Dispatch client request
Dispatcher::execute( Dispatcher::dispatch() );
?>