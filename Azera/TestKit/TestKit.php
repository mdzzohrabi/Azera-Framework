<?php
namespace Azera\TestKit;

use Azera\Util\ObjectReflector;
use Azera\IO\Response;

class TestKit
{

	public  $version 	= '1.0';

	private $tests 	= array();
	
	function eq( $a , $b )
	{
		return ( $a == $b );
	}

	function startup()
	{
		# user code here
	}

	function results()
	{
		return $this->tests;
	}

	function startLog( $name , $comment , $result = false )
	{
		$this->tests[ $name ] = array(
				'comment'	=> $comment,
				'commentArgs'	=> array(),
				'time'		=> microtime(true),
				'totalTime'	=> microtime(true),
				'memory'	=> memory_get_usage(),
				'totalMemory'=> memory_get_usage(),
				'result'	=> null,
				'class'		=> 'pass'
			);
		if ( $result )
			$this->tests[$name]['commentArgs']['result'] = $result;
	}

	function logResult( $name , $result )
	{
		$this->tests[ $name ]['result'] 	= $result;
	}

	function endLog( $name )
	{
		$test 	= $this->tests[$name];
		$this->tests[ $name ] = array_merge( $test , array(
				'time'			=> microtime(true) - $test['time'],
				'totalTime'		=> microtime(true),
				'memory'		=> memory_get_usage() - $test['memory'],
				'totalMemory'	=> memory_get_usage(),
				'class'			=> ( isset($test['commentArgs']['result']) && $test['result'] != $test['commentArgs']['result'] ? 'fail' : 'pass' )
			));
	}

	function __construct()
	{

		$this->startup();

		$methods 	= get_class_methods($this);

		$reflect 	= new ObjectReflector( $this );

		foreach ( $reflect->getMethods() as $method )
		{
			$name 		= $method->name;
			$comment 	= $method->getDocComment();

			if ( substr( $name , 0 , 4 ) != 'test' ) continue;

			$comment 	= (object)$this->comment( $comment );

			$memory 	= memory_get_usage();
			$time 		= microtime(true);

			$result 	= $this->{$name}( $comment->args );

			$time 		= microtime(true) - $time;

			if ( is_object($result) )
				$result 	= (array)$result;

			if ( is_bool($result) )
			{
				ob_start();
				var_dump($result);
				$result = ob_get_clean();
			}

			if ( is_array( $result ) )
			{
				ob_start();
				var_dump($result);
				$result = ob_get_clean();
			}

			$memory 	= memory_get_usage() - $memory;

			$class 	= 'pass';

			if ( isset($comment->args['result']) )
			{
				if ( $comment->args['result'] != $result )
					$class = 'fail';
			}

			$this->tests[ $name ] = array(
					'result'		=> $result,
					'comment'		=> $comment->comment,
					'commentArgs'	=> $comment->args,
					'memory'		=> $memory,
					'totalMemory'	=> memory_get_usage(),
					'time'			=> $time,
					'totalTime'		=> microtime(true),
					'class'			=> $class
				);



		}

	}

	static function comment( $comment )
	{
		$temp 		= array_filter( array_map( 'trim' , explode( "\n" , str_replace( array('*','/') , '' , $comment ) ) ) );
		$comment 	= array();
		$args 		= array();
		foreach ( $temp as $line )
		{
			if ( substr( $line , 0 , 1 ) == '@' )
			{
				list( $key , $value ) 	= explode(' ' , $line , 2);
				$args[ substr($key,1) ] = trim($value);
			}else
			{
				$comment[] = $line;
			}
		}
		$comment 	= implode("\n" , $comment);
		return compact('comment','args');
	}

	static function run( $class )
	{
		Response::write('<link rel="stylesheet" href="/testkit/testkit.css"/>');

		$tests 	= new $class();
		$tests 	= $tests->results();

		Response::write('<table class="testkit">');

		Response::write('<thead><tr>
			<th>Method</th>
			<th>Comment</th>
			<th>Result</th>
			<th>Result Type</th>
			<th>Memory</th>
			<th>Total Memory</th>
			<th>Time</th>
			<th>Total Time</th>
			</tr></thead>');


		foreach ( $tests as $name => $test )
		{
			$test 	= (object)$test;
			Response::write('<tr class="' . $test->class . '">
				<td><pre>' . $name . '()</pre></td>
				<td>' . nl2br($test->comment) . '</td>
				<td>' . $test->result . '</td>
				<td>' . gettype($test->result) . '</td>
				<td>' . $test->memory . ' bytes</td>
				<td>' . $test->totalMemory . ' bytes</td>
				<td>' . sprintf('%.4f',$test->time * 1000) . ' ns</td>
				<td>' . $test->totalTime . '</td>
				</tr>');
		}

		Response::write('</table>');
	}

}
?>