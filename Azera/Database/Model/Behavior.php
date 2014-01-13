<?php
namespace Azera\Database\Model;

use Azera\Core\Object;

class Behavior extends Object {

	public $model 	= null;

	function __construct( $config = array() )
	{
		parent::__construct();
		$this->model 	= $config['model'];
	}

	function map()
	{
		$methods 	= get_class_methods($this);
		$parent 	= get_class_methods('Object');
		$parent[] 	= 'map';
		$methods 	= array_diff( $methods  , $parent);
		return $methods;
	}

}
?>