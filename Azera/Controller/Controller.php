<?php
namespace Azera\Controller;

use Azera\Core\Object;
use Azera\View\View;
use Azera\Core\Process;

class Types
{
	const 	Controller 	= 1;
	const 	Page 		= 2;
	const 	Block 		= 3;
	const 	Other 		= 0;
	const 	Area 		= 4;
}

class Controller extends Object
{

	/**
	 * Needed Components
	 */
	public $uses 	= array();

	/**
	 * Needed Models
	 */
	public $models 	= array();

	/**
	 * Nedeed Helpers
	 */
	public $helpers 	= array();

	/**
	 *	View name
	 * 	e.g 	index 	| 	add 	| 	section.add
	 * 	@string
	 */
	public $view 		= null;
	public $viewVars 	= array();
	public $viewTags 	= array();

	/**
	 *	Controller Type
	 * 	@const
	 */
	public $controllerType 	= Types::Controller;

	/**
	 *	Alias to application document (Area)
	 *	@object
	 */
	public $document 	= null;

	/**
	 * Owner Object
	 * @var 	object
	 */
	public $owner 		= null;

	/**
	 * Passed Arguments to Controller By Request Url
	 * @var 	Array
	 */
	public $passedArgs 	= array();

	/** Startup Function **/
	public function startup()
	{
		# User Code Here
	}

	public function __construct( $owner = null )
	{
		parent::__construct();

		$this->owner 	= &$owner;

		$this->document 	= Process::area();

		$this->startup();
	}
	
	public function addTag( $tag , $function = null )
	{

		if ( is_array( $tag ) )
		{
			$this->viewTags 	= array_merge( $this->viewTags , $tag );
			return $this;
		}

		$this->viewTags[ $tag ] 	= $function;

		return $this;
	}
	
	public function render()
	{

		$View 	= new View( $this , $this->view , $this->viewVars );

		$View->addTag( $this->viewTags );

		return $View->render();

	}

	/**
	 *	Pass variable to view
	 */
	public function set( $key  , $value = null )
	{
		if ( is_array( $key ) )
			$this->viewVars 	= array_merge( $this->viewVars , $key );
		else
			$this->viewVars[$key] 	= $value;
	}

}
?>