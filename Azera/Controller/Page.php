<?php
namespace Azera\Controller;

use Azera\Util\ObjectReflector;

class Page extends Controller
{
	
	public $controllerType 	= Types::Page;			// Controller Type

	public $Area 			= null; 				// Point to Area Object

	/**
	 * Requested Action
	 * @var 	string
	 */
	public $action 		= null;

	/**
	 * List Of Pointers Variables
	 */
	public function pointers()
	{

		return array(
				'title'		=> &$this->Area->title,
				'tags'		=> &$this->Area->tags,
				'theme'		=> &$this->Area->theme
			);

	}

	public function __construct( $owner , $action = null , $args = array() )
	{

		parent::__construct( $owner );

		$this->action 	 = $action;
		$this->args 	 = $args;

		$this->Area 	= &$this->owner;

	}

	public function hasMethod( $method )
	{
		$private 	= get_class_methods('Azera\Controller\Page');

		$methods 	= get_class_methods($this);

		$methods 	= array_diff( $methods, $private);

		return in_array($method,$methods);
	}

	public function invokeAction( $action , $args = array() )
	{
		if ( $this->hasMethod($method) )
		return call_user_func_array(array($this,$method), $args);
		return false;
	}

}
?>