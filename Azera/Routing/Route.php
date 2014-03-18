<?php
namespace Azera\Routing;

use Azera\Core\Basic\Object;
use Azera\Core\Config;
use Closure;

class Route
{

	use Object;

	const ACTION 		= 'action';
	const ARGS 			= 'args';
	const CONTROLLER 	= 'controller';
	const LANG 			= 'lang';

	static private $_wheres 	= array(
			'action'		=> '[a-zA-Z_]+[0-9]*',
			'args'			=> '.+',
			'year'			=> '[12][0-9]{3}',
			'month'			=> '[01]?[0-9]',
			'day'			=> '(0[1-9]|[123][0-9])'
		);

	static function define( $name , $regex )
	{
		self::$_wheres[$name] 	= $regex;
	}

	static function wheres()
	{
		return self::$_wheres;
	}

	var $pattern 	= null;

	var $wheres 	= [];

	var $route 		= null;

	var $redirect	= null;

	var $hasAction 	= false;

	var $hasArgs 	= false;

	var $area 		= null;

	var $args 		= [];

	var $passedArgs = [];

	var $defaults 	= [];

	var $filters 	= [];

	var $acceptMethods = false;

	private $regex 	= null;

	function __construct( array $options )
	{

		$this->filters 	= Router::filters( array_flip(Config::get('Routing.Default.Filters')) );

		$options 	= array_merge( [
				'pattern' 		=> null,
				'route'			=> null,
				'passedArgs'	=> null,
				'redirect'		=> null,
				'area'			=> Config::get('Routing.Default.Area'),
				'args'			=> []
			] , $options );

		foreach ( [ 'pattern' , 'route' , 'redirect' , 'area' , 'args' , 'passedArgs' ] as $key )
			$this->{$key} 	= $options[$key];

		// Add / to first of pattern
		if ( substr( $this->pattern , 0 , 1 ) != '/' )
		{
			$this->pattern 	= '/' . $this->pattern;
		}

		// Convert pattern to regexp
		$this->createRegexp();
	}

	function createRegexp()
	{
		$_temp 	= strtr( $this->pattern , [
					'/'	=> '\/',
					'*'	=> '{args?}'
			]);

		$wheres	= $this->wheres + self::wheres();

		$_temp 	= preg_replace_callback('@(?P<p>\\\/|.)(:(?P<a>[\w]+)(?P<c>[?]?)|{(?P<b>[\w]+)(?P<d>[?]?)})@', function ( $match ) use ( &$wheres ) {

			$name 	 	= !empty($match['b']) ? $match['b'] : $match['a'];

			$optional 	= !empty($match['c']) | !empty($match['d']);

			$filter 	= $wheres[$name] ? $wheres[$name] : '\w+';

			return "(?:" . $match['p'] . "(?P<$name>$filter)" . ( $optional ? '?' : null ) . ")" . ( $optional ? '?' : null );

		}, $_temp );

		$_temp 	= "^$_temp$";

		$this->regex 	= $_temp;		
	}

	/**
	 * Create filter
	 * Route::with('auth' , Closure );
	 * Route::with('method');
	 */
	function with($name,$callable = null)
	{

		if ( is_null($callable) )
		{
			$this->filters[$name] 	= Router::filter($name);
			return $this;
		}

		$this->filters[$name] 	= $callable;

		return $this;
	}

	function area($name)
	{
		$this->area 	= $name;
		return $this;
	}

	function has( $filters )
	{
		$result 	= true;
		foreach ( $filters as $key => $filter )
			$result 	&= strtolower($this->{$key}) == strtolower($filter);
		return $result;
	}

	function where( $argument , $regexp = null )
	{
		if ( is_array($argument) )
		{
			$this->wheres 	+= $argument;
		}else
		{
			$this->wheres[ $argument ]  = $regexp;
		}

		$this->createRegexp();

		return $this;
	}

	function set( $name , $value = null )
	{

		if ( is_array($name) )
			$this->defaults 	+= $name;
		else
			$this->defaults[$name] 	= $value;

		return $this;

	}

	function filter( $uri )
	{
		if ( preg_match( '/' . $this->regex . '/' . ( Config::get('Routing.CaseSensitive') ? 'i' : null) , $uri , $matches ) )
		{
			$result 	= [];
			$matches 	= array_filter($matches);

			foreach ( $matches as $key => $value )
				if ( !is_int($key) )
					$result[$key] 	= $value;

			$result 	+=	(array)$this->defaults;

			// Check filters
			foreach ( $this->filters as $filter )
				if ( !$filter( $this , $result ) )
					return false;

			return $result;
		}
		return false;
	}

	function accept( $methods = [] )
	{
		$this->acceptMethods 	= (array)$this->acceptMethods + (array)$methods;

		return $this;

	}

	function route()
	{
		return [
			'route'		=> $this->route,
			'args'		=> $this->args,
			'passedArgs'=> $this->passedArgs
		];
	}

	function __toString()
	{
		return $this->pattern;
	}

}
?>