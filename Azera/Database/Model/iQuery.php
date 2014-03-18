<?php
namespace Azera\Database\Model;

use Azera\Database\Model;
use Azera\Util\Set;

/**
 * Singeletion Query class
 * @package Azera\Database\Model
 * @param  App\Bundle\Model     $model  - Model object
 * @return query result
 * @author Masoud Zohrabi ( @mdzzohrabi )
 * */
class iQuery
{
	private $model 		= null;
	private $o 			= array();

	function __construct( Model &$model )
	{
		$this->model 	= $model;
	}

	function model()
	{
		return $this->model;
	}

	function with()
	{
		$this->o['contain']		= func_get_args();
		return $this;
	}

	function sort( $sort )
	{
		$this->o['sort'] 	= $sort;
		return $this;
	}

	function where( $cond = array() )
	{
		$this->o['conditions']	= merge( (array)$this->o['conditions']  , (array)$cond );
		return $this;
	}

	function find( $type = 'all' , $o = array() )
	{
		return $this->model->find( $type , array_merge( $this->o , $o ) );
	}

	function first( $o = [] )
	{
		return $this->find( 'first', $o );
	}

	function count( $o = array() )
	{
		return $this->model->find( 'count' , array_merge( $this->o , $o ) );
	}

	function one( $o = array() )
	{
		return $this->find( 'first', $o );
	}

	function all( $o = array() )
	{
		return $this->find( $o );
	}
}
?>