<?php
namespace App\Area;

use Azera\Controller\Area;
use Azera\Core\AreaManager;

class Home extends Area
{
	
	public static $area 	= array(
			'name'			=> 'Home',
			'routePrefix'	=> '',
			'title'			=> 'Main Website Area',
			'class'			=> 'App\Area\Home'
		);

	public $title 	= 'Website HomePage';

	public function startup()
	{

		if ( $this->dispatch )
		{
			
		}

		$this->set('title', $this->title );

		$me 	= &$this;

		$this->addTag('title',function ( $tag ) use ( &$me )
		{
			return \Html::tag('title', $me->title );
		});

	}

}

AreaManager::add( Home::$area );

?>