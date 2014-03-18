<?php
namespace Azera\View\Parser;

use Azera\View\View as ViewObject;
use Cache;
use String;

class View
{
	
	var $blocks 	= [];
	var $vars 		= [];
	var $view 		= null;
	var $html 		= null;
	var $output 	= null;

	var $regex 		= [

		// {{--Comment--}}
		'{{--(?P<comment>(?:.|\s)*?)--}}' 											=> '_comment',
		// {$name}
		// {Form.close()}
		// {$name.reza}
		// {$object.result()}
		//'{(?P<var>\$?)(?P<key>(?:[a-zA-Z]+[0-9]*\.?)+)(?P<fn>\s*\(.*\))?}'			=> '_print',
		// #function args....
		'^\h*\#(?P<fn>\w+)\h+(?P<args>.*)$'											=> '_function',
		// @section arg
		// [content]
		// @end
		'\h*@(?P<section>block)(?:\h+(?P<label>\w+))?\s(?P<content>(?:.|\s)*?)(?:@end)\b'						=> '_section'

	];

	var $sections 	= [];

	var $argsRegex 	= '/\[.*\]|(["\'])(.*?)(?<!\\\)\1|[a-zA-Z0-9.]+/i';

	var $returns 	= [];

	function __construct( ViewObject &$view , $html , $vars = [] )
	{

		$this->view 	= &$view;
		$this->html 	= $html;
		$this->vars 	= $vars;

	}

	function returns( $data )
	{
		$this->returns[] 	= $data;
	}

	function &with( $var , $value = null )
	{
		if ( is_array( $var ) )
		{
			$this->vars 	= array_merge( $this->vars , $var );
		}else
		{
			$this->vars[$var] 	= $value;
		}
		return $this;
	}

	function parse()
	{
		
		$this->output 	= $this->html;

		foreach ( $this->regex as $regexp => $method )
		{
			$this->output 	= preg_replace_callback('/' . $regexp . '/im', array( $this , $method ) , $this->output );
		}

		$this->output 	= strtr( $this->output , [
				'{{'	=> '<?=',
				'}}'	=> ';?>'
			] );

		$this->output 	.= "\n" . '<?php return unserialize(base64_decode("' . base64_encode(serialize($this->returns)) . '"));?>';
		
		return $this->output;
	}

	private function parseArgs( $input )
	{
		preg_match_all($this->argsRegex, $input, $m);
		return $m[0];
	}

	function _comment( $input )
	{
		return '<!--' . $input['comment'] . '-->';
	}

	function _function( $input )
	{

		$args 	= implode( ',' , $this->parseArgs( $input['args'] ) );

		$func 	= $input['fn'];

		return "<?php \$this->call('$func',__LINE__,[$args]);?>";

	}

	function _print( $input )
	{
		return '[PRINT]';
	}

	function _section( $input )
	{

		extract($input);

		$content 	= preg_replace_callback('/@(?P<type>content|parent)\b/i', function( $match ) use ( &$label ){

			return '<?=$this->__section["' . $label . '"]["' . $match['type'] . '"];?>';

		}, $content);

		return '<?php if ( !in_array("' . $label . '",$this->filters) ): ?>' . $content . '<?php endif; ?>';

	}

}
?>