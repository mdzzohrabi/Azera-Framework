<?php
namespace Azera\Util;

class ListArray {
	
	private $items 	= array();

	public function __construct( $data = array() )
	{
		$this->import($data);
	}

	public function length()
	{
		return count($this->items);
	}

	public function size()
	{
		return $this->length();
	}

	public function add( $data )
	{
		$this->items[]	= $data;
		return $this;
	}

	public function insert( $data )
	{
		return $this->add($data);
	}

	public function delete( $keyIndex )
	{
		unset($this->items[$keyIndex]);
		return $this;
	}

	public function import( $data = array() )
	{
		$this->items 	= array_merge( $this->items , $data );
		return $this;
	}

	public function get( $keyIndex )
	{
		if ( !isset($this->items[$keyIndex]) ) return false;
		return $this->items[$keyIndex];
	}

	public function join($str = ' ')
	{
		return implode($str,$this->items);
	}

	public function gets()
	{
		return $this->items;
	}

	public function last()
	{
		return end($this->items);
	}

	public function first()
	{
		return first($this->items);
	}

	public function next()
	{
		return next($this->items);
	}

	public function prev()
	{
		return prev($this->items);
	}

}
?>