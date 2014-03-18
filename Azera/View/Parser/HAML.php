<?php
namespace Azera\View\Parser;

use Azera\IO\File;

class HAML
{

	private $content 	= null;
	private $file 		= null;

	private $stack			= array();
	private $lastLevel 		= 0;
	private $lastTagLevel	= 0;
	private $maxLevel 		= 0;
	private $currentTag 	= array();
	private $isBlock		= false;
	private $block 			= array();

	private $blocks 		= array();

	private $defaultTag 	= 'div';

	const 	TOKEN_TYPES 	= 	'=#@%-!{}?+';
	const 	TOKEN_DOCTYPE 	=	'!!!';
	const 	TOKEN_BLOCK 	=	'(?P<block>\w+)\s*:\s*$';
	const 	TOKEN_END_BLOCK	= 	'^\\s*$';

	private $nonContentTags 	= array(
			'img','meta'
		);

	private $docTypes 	= array(
			'1.1' 			=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
			'Strict' 		=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
			'Transitional'	=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
			'Frameset'		=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
			'XML'			=> "<?php echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>'; ?>\n",
			'html5'			=> '<!doctype html>'
		);

	private $regexp 	= array();

	function addRegexp( $pattern , $callback )
	{
		$this->regexp[$pattern] 	= $callback;
	}
	
	function __construct( $file = null )
	{

		if ( $file )
		{
			$this->file 	= $file;
			$this->content 	= File::readAll( $file );
		}

		$this->addBlock('javascript',function( $script , $level )
		{
			$out 	= null;
			foreach ( $script as $l )
				$out 	.= str_repeat("\t", $level) . $l . NL;

			return '<script type="text/javascript">' . NL
			 	 	. $out
			 	 	. str_repeat("\t", $level) . '</script>';
		});

		$this->addBlock('css',function( $css , $level ){
			$out 	= null;
			foreach ( $css as $l )
				$out 	.= str_repeat("\t", $level) . $l . NL;
			return '<style>' . NL
			 	 	. $out
			 	 	. str_repeat("\t", $level) . '</style>';
		});

	}

	function addBlock( $name , $callback )
	{
		$this->blocks[ $name ] 	= $callback;
	}

	function renderScript( $content , $line = 0 , $level = 0 )
	{

		if ( preg_match('/^\s*if\s*(?P<cond>.*)$/', $content, $if) )
		{
			$content 	= 'if (' . $if['cond'] . '){';

			$this->stack[$level][] = array( 'raw' => '<?php };?>' , 'line' => $line );

		}else if ( preg_match('/^\s*else\s*$/', $content) )
		{
			$content 	= 'else{';

			$this->stack[$level][] = array( 'raw' => '<?php };?>' , 'line' => $line );

		}

		return '<?php ' . $content . ';?>';
	}

	function renderContent( $content )
	{

		// Object function call
		if ( preg_match('/^\=(?P<map>[a-zA-Z0-9.]+\(.*\))$/', $content,$match) )
		{
			return '<?=$' . str_replace('.','->',$match['map']) . ';?>';
		}

		// Array Variable
		if ( preg_match('/^\=(?P<map>[a-zA-Z0-9.]+)(?:(?:\s*\>\s*)(?P<func>\w+))?$/', $content,$match) )
		{
			$parts 	= explode( '.' , $match['map'] );

			$var 	= '$' . current($parts) . (count($parts) > 1 ? "['" . implode( "']['" , array_slice($parts, 1) ) . "']" : null);

			if ( !empty($match['func']) )
				$var  	= $match['func'] . '(' . $var . ')';

			return '<?=' . $var . ';?>';
		}

		if ( preg_match('/^=(?P<string>[a-zA-Z0-9\s]+)$/', $content , $match) )
		{
			return '<?=__("' . $match['string'] . '");?>';
		}

		return $content;
	}

	function parse()
	{
		$lines 	= explode( NL, $this->content );
		$out 	= null;
		$length	= count($lines);

		$n 		= 0;

		foreach ( $lines as $line )
		{

			$n++;

			// END of Block
			if ( preg_match( '/' . self::TOKEN_END_BLOCK . '/' , $line) )
			{
				$this->isBlock = false;
				if ( isset( $this->blocks[ $this->block['name'] ] ) )
				{
					$out 	.= $this->blocks[ $this->block['name'] ]( $this->block['content'] , $this->block['level'] , $this->block['line'] );
					$this->block 	= array();
				}
				continue;
			}

			// Block Content
			if ( $this->isBlock )
			{
				$this->block['content'][] 	= substr( $line , $this->block['level'] + 1 );
				continue;
			}

			// Empty Line
			if ( trim($line) == '' )
			{
				$out 	.= NL . str_repeat("\t", $this->lastLevel );
				continue;
			}

			// Regexp for find line level
			preg_match('/^(?P<level>\t*)/', $line, $level);

			// Line level
			$level 	= substr_count( $level['level'] , "\t" );

			if ( $level <= $this->lastTagLevel )
			{
				$out 	.= $this->printEndFromStack( $level , $n );
			}

			$out	.= NL . str_repeat("\t", $level);

			$out 	.= $this->parseLine( $line , $level , $EOL , $n );

			// Set last line level
			$this->lastLevel 	= $level;

			$this->maxLevel 	= max( $level , $this->maxLevel );

		}

		foreach ( $this->stack as $tag )
		{
			$out 	.= $this->printEndFromStack();
		}

		foreach ( $this->regexp as $regexp => $callback )
		{
				$out 	= preg_replace_callback($regexp, $callback, $out);
		}

		return $out;

	}

	function printEndFromStack( $level = 0 , $line = false )
	{
		if ( !empty($this->stack) )
		{
			$out 	.= null;
			for ( $i = $this->maxLevel ; $i >= $level ; $i-- )
				if ( !empty($this->stack[$i]) )
				{
					while ( $tag = array_pop( $this->stack[$i] ) )
					{
						$content 	= null;

						if ( $tag['tag'] ) $content 	= "</" . $tag['tag'] . ">";

						if ( $tag['raw'] ) $content 	= $tag['raw'];

						$out 	.= ($line && $line - $tag['line'] == 1 ? null : NL . str_repeat("\t", $i)) . $content;
					}
				}
			return $out;
		}
		return null;
	}

	function parseLine( $line , $level = 0 , $EOL = false , $number )
	{

		// Block Start
		if ( preg_match('/' . self::TOKEN_BLOCK . '/', $line, $block) )
		{
			$this->isBlock 	= true;
			$this->block 	= array(
					'name' 		=> $block['block'],
					'line'		=> $number,
					'level'		=> $level,
					'content'	=> array()
				);
			return null;
		}

		// HTML Tag
		if ( strspn(substr( trim($line) , 0 , 1 ) , self::TOKEN_TYPES ) == 0 )
		{
			preg_match('/(?P<level>\t*)(?P<tag>[a-zA-Z0-9]*)(?P<class>[\.\w]*)(?P<id>#[^\s%#!=*^&]+){0,1}(?P<attrs>(?:(?:\s+[^\s\=]+\=(?:\".+\"|[^\s\=]+))*))(?P<inner>.*)/', $line, $match);

			if ( empty($match['tag']) )
			{
				$match['tag'] 	= $this->defaultTag;
			}

			$closedTag 	= in_array( $match['tag'] , $this->nonContentTags );

			if ( !$closedTag )
			{
	
				$this->stack[$level][] = array( 'tag' => $match['tag'] , 'line' => $number );

				$this->lastTagLevel 	= $level;

			}

			$this->currentTag = array(
					'tag'	=> $match['tag']
				);

			return $this->printTag( $match, $closedTag );
		}

		// DOCTYPE
		if ( preg_match('/^\s*' . self::TOKEN_DOCTYPE . '\s+(?P<type>.*)/', $line , $match) )
		{
			return $this->docType( $match );
		}

		// Tag Content
		if ( preg_match('/^\+(?P<content>.*)/', trim($line) , $match) )
		{
			return $this->renderContent( $match['content']);
		}

		// Script
		if ( substr( trim($line) , 0 , 1 ) == '-' )
		{
			return $this->renderScript( substr( trim($line) , 1 ) , $number , $level );
		}

		return null;

	}

	function docType( $doc )
	{
		return $this->docTypes[ $doc['type'] ];
	}

	function printTag( $tag , $closedTag = false )
	{

		extract($tag);

		$class 	= empty($class) ? null : ' class="' . trim(str_replace('.',' ',$class)) . '"';

		$id 	= empty($id) 	? null : ' id="' . str_replace('#','',$id) . '"';

		$inner 	= empty($inner)	? null : $this->renderContent( substr( $inner , 1) );

		$end 	= $closedTag ? "/>" : '>';

		return "<$tag$class$id$attrs$end$inner";

	}

	function __call( $fn , $args )
	{
		if ( substr($fn,0,3) == 'get' )
		{
			$method 	= strtolower(substr( $fn , 3 ));

			if ( method_exists($this, $method) )
			{
				return $this->{$method};
			}
			return false;
		}
		if ( substr($fn,0,3) == 'set' )
		{
			$method 	= strtolower(substr( $fn , 3 ));
			return $this->{$method} 	= current($args);
		}
	}

}
?>