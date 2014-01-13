<?php
/**
 * Azera System Core Startup
 * @author Masoud Zohrabi ( @mdzzohrabi )
 * @package Azera\System
 */
namespace Azera\System;

use Azera\Core\Startup as BaseStartup;
use Azera;
use Azera\Core\AreaManager;
use Azera\Core\Loader;
use Azera\Core\Apps;
use Azera\Database\Database;
use Azera\Core\Config;
use Azera\Routing\Router;

class Startup extends BaseStartup
{

	public function start()
	{

		// Define Debug Constant
		define('__DEBUG__' ,  Config::get('Debug') );

		// Initialize Database
		Database::init();

		// Initialize Areas
		AreaManager::init();

		// Fetch Apps From Database
		$apps 	= Loader::Model('System.Apps' , true)->findAll();

		// Add System Core Bundle
		Apps::add('System');

		foreach ($apps as $app)
		{
			Apps::add( $app['App']['name'] );	# Add App to Apps Manager
		}

		if ( __DEBUG__ )
		{
            # Draw Debugging Toolbar
			Azera::onEnd('Azera\Debug\Toolbar::draw');
		}

	}

}

return new Startup();
?>