<?php
namespace Azera\Database\Model;

use Azera\Database\Model;
use Azera\Util\ClassRegistry;
use Azera\Core\Object;

class BehaviorCollection extends Object
{

	private $model 	= null;
	private $_items	= array();
	private $maps 	= array();
	
	function __construct( Model &$model )
	{
		parent::__construct();
		$this->model 	= $model;
	}

	/**
	 * dispatch and call a method
	 * @param 	String 	$method
	 * @param 	Array 	$args
	 * @return 	mixed
	 */
	public function dispatchMethod( $method , $args = array() )
	{
		foreach ($this->maps as $object => $methods) {
			if ( in_array( $method , $methods ) )
				return call_user_func_array( array( $this->_items[$object] , $method ) ,  $args );
		}
	}

	/**
	 * Create a map from and Behavior
	 * @param 	Object 	$object
	 * @return 	Object
	 */
	public function &map( &$object )
	{
		$this->maps[ (string)$object ] 	= $object->map();
		return $object;
	}

	/**
	 * Initialize Behaviors
	 * @param 	String 	$name
	 * @param 	Array 	$config
	 * @return 	Array 	Collector of Behaviors
	 */
	public function init( $name , $config = array() )
	{

		if ( is_array( $name ) )
		{
			foreach ($name as $key => $conf ) {
				if ( !is_array($conf) )
				{
					$key 	= $conf;
					$conf 	= array();
				}
				$this->init( $key , $conf );
			}
			return;
		}

		$config['model']	= &$this->model;


		// Is set in the past ?
		if ( isset($this->_items[$name]) ) return $this->_items[$name];

		if ( $behavior = ClassRegistry::register( array("lib.bundle.model.behaviors.{$name}" , "App.Bundle.Model.Behaviors.{$name}") , $config ) )
			{
				return $this->_items[ (string)$behavior] = $this->map( $behavior );
			}

		list( $behavior , $module , $bundle ) 	= array_reverse( explode( '.' , $name ) );
		$vars 	= extend( array(
				'module'	=> $this->model->moduleName,
				'bundle'	=> $this->model->bundleName
			) , compact('behavior','module','bundle') ); 
		extract($vars);

		$class 	= "Bundle\{$bundle}\{$module}\Model\Behaviors\{$behavior}";
		$behavior 	= strtolower( $behavior );
		$path 	= "bundles.{$bundle}.{$module}.models.behaviors.{$behavior}";

		return $this->_items[ $class ] = $this->map( ClassRegistry( array( $path , $class ) , $config ) );

	}

}
?>