<?php
namespace Azera\Database;

abstract class Result
{
	public $num_rows 	= 0;
	public $count 		= 0;
	public $field_count	= 0;
	public $result 		= null;

	abstract function read();

	public function fetch()
	{
		return $this->read();
	}

	public function __construct( $result = null )
	{

	}

}

?>