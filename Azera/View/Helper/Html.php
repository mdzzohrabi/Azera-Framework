<?php
namespace Azera\View\Helper;

class Html
{
	
	const CLOSED 	= 'closed';
	const OPEN 		= 'open';
	
	function charset( $charset )
	{
		return self::tag('meta',array(
			'http-equiv'	=> 'Content-Type',
			'content'		=> 'text/html;charset=' . $charset
		));
	}

	function tag( $tag , $content = null , $attrs = array() , $close 	= true )
	{
		$type 	= self::OPEN;
		if ( is_array($content) )
		{
			$type 	= self::CLOSED;
			$attrs 	= $content;
		}

		$attrs 	= self::attrs( $attrs );

		if ( $type == self::OPEN )
			return "<$tag$attrs>$content" . ($close ? "</$tag>" : null ) . NL;
		return "<$tag$attrs" . ( $close ? "/" : null ) . ">" . NL;
	}

	function attrs( $attrs = array() , $join = '=' , $qoute = '"' )
	{
		$out 	= array();

		foreach ( $attrs as $attr => $value )
		{
			if ( is_array($value) )
				$value 	= self::attr( $value , ':' , null );
			$out[] 	= "$attr$join$qoute$value$qoute";
		}

		return ( !empty($out) ? ' ' . implode(' ' , $out) : null );

	}

	function css( $src , $attrs = array() )
	{
		if ( substr($src,0,7) != 'http://' )
			$src 	= asset($src);
		return self::tag('link', array_merge(array(
				'href'	=> $src,
				'rel'	=> 'stylesheet'
			) , $attrs ));
	}

	function js( $src , $attrs = array() )
	{
		if ( substr($src,0,7) != 'http://' )
			$src 	= asset($src);
		return self::tag('script','', array_merge(array(
				'src'	=> $src,
				'type'	=> 'text/javascript'
			) , $attrs ));
	}



}
?>