<?php
namespace Azera\Util\Parser;

use Azera\Util\String;
use Azera\Util\XML;
use Azera\Cache\Cache;

class Html
{

	/**
	 * Create New HtmlParser Object
	 * @param 	$content 	Html Code
	 */
	public static function getInstance( $content )
	{
		return new self( $content );
	}

	/**
	 * List Of Reerved Tags to Parse From Content
	 * @var 	Array
	 */
	private $reservedTags = array();

	private $orginalHtml 	= null;

	/**
	 * Add New Reserved Tag To List
	 * @param 	$tag 	Tag name 		Ex. html 	to Parse <html> tags
	 */
	public function addTag( $tag , $function = null )
	{
		if ( is_array( $tag ) )
		{
			$this->reservedTags 	= array_merge( $this->reservedTags , $tag );
			return $this;
		}

		$this->reservedTags[ $tag ] 	= $function;

		return $this;
	}

	function tagAttrs( $innerTag )
	{
		preg_match_all( '/(\w+)="([^"]*)"/' , $innerTag, $matches);
		$attrs 	=  array();
		foreach ( $matches[1] as $i => $attr )
		{
			$value 	= $matches[2][ $i ];
			$attrs[ $attr ] 	= $value;
		}
		return $attrs;
	}

	function __construct( $content )
	{
		$this->orginalHtml 	= $content;
	}

	/**
	 * Parse Tags From Content
	 * @return Array
	 */
	public function parse( $content = null )
	{		

		$content 	= $content ? $content : $this->orginalHtml;

		//$sha 	= 'html_parsed_' . sha1( $content . implode('', array_keys($this->reservedTags));		// Generate Content Hash Checkshum

		/** Read Parsed Data From Cache **/
		//if ( $result = Cache::read( $sha ) )
		//{
		//	return (object)$result;
		//}

		//$result 	= array(
		//		'tags'	=> array(),			// Array Of Founded Tags
		//		'length'=> 0				// Number Of Tags
		//	);

		$i 			= 0;

		$org 		= $content;

		foreach ( $this->reservedTags as $tag => $function ) {

			$content 	= $org;
			$from 		= 0;

			# Find Tags
			while ( ($start = String::indexOf( $content , "<$tag " )) !== false || ($start = String::indexOf( $content , "<$tag/>" )) !== false || ($start = String::indexOf( $content , "<$tag>" )) !== false )
			{


				$i++;	# Tags Count Add

				$temp 	= substr( $content , $start ); 	 	# Get Content From Start of Tag

				$temp2 	= preg_replace_callback( '/("[^"]*")/' , function( $match ){
					return str_repeat( '*' ,  strlen( $match[0] ) );
				} , $temp);

				if ( String::indexOf( $temp2 , '>' ) !== false && String::indexOf( $temp2 , '/>' ) !== false )
					$type 	= ( String::indexOf( $temp2 , '>') < String::indexOf( $temp2 , '/>' ) ? 'open' : 'closed' );
				elseif ( String::indexOf( $temp2 , '>' ) )
					$type 	= 'open';
				else
					$type 	= 'close';

				$tagLen = strlen("<$tag");

				if ( $type == 'closed' )
				{
					$innerTag 	= substr( $temp , $tagLen , String::indexOf( $temp2 , '/>' ) - $tagLen );
					$attrs 	 	= $this->tagAttrs( $innerTag );
					$innerHtml 	= null;
					$tagHtml 	= "<$tag $innerHtml/>";
				}
				else
				{

					$after 	= substr( $content , $start );

					$after 	= preg_replace_callback( '/("[^"]*")/' , function( $match ){
					return str_repeat( '*' ,  strlen( $match[0] ) );
					} , $after);
					# Find After Open Tags And Close Tags
					preg_match_all( '/((<' . $tag . '[^\/>]*>)|(<\/' . $tag . '>))/' , $after, $tmp);
					$n 	= 0;
					$f 	= 0;
					foreach ( $tmp[1] as $s )
					{
						if ( substr($s,1,1) != '/' )
						{
							$n++;
							continue;
						}
						$f++;
						$n--;
						if ( $n == 0 )
							break;
					}
					# Find End Tags
					$endTag 	= false;
					$index 		= 0;
					$p 			= 0;
					$n 			= 0;
					while ( ( $estart = String::indexOf( substr( $after , $index ) , "</$tag>" ) ) !== false )
					{
						$n++;
						if ( $f == $n )
						{
							$endTag = $p + $estart;
							break;
						}
						$p 		+=	$index 	= $estart + 1;
					}
					$endTag 	+= $start;
					$innerTag 	= substr( $temp , $tagLen , String::indexOf( $temp2 , '>' ) - $tagLen );
					$attrs 		= $this->tagAttrs( $innerTag );
					$innerHtml 	= substr( $content , $start + strlen("<$tag $innerTag>") - 1 , $endTag - $start - strlen("<$tag $innerTag>") + 1 );

					//$innerHtml 	= $this->parse( $innerHtml );
					$tagHtml 	= "<$tag$innerTag>$innerHtml</$tag>";

					//if ( strpos($innerHtml,'<') !== false )
					//	$innerHtml	= $this->parse( $innerHtml );
				}

				$tagObj 		= (object)array(
					'html' 		=> $tagHtml,
					'attrs'		=> $attrs,
					'innerHtml'	=> $innerHtml,
					'tagType'	=> $type,
					'name'		=> $tag
				);

				$len 	= strlen( $tagObj->html );

				$_tag 	= $this->reservedTags[ $tag ]( $tagObj );

				$org 	= substr( $org , 0 , $start + $from ) . $_tag . substr( $org , $start + $from + $len + 1 );

				//return $org;
				//if ( $i == 2 )
				//	return $org;

				$from 	+= $start + ( strlen($_tag) - $len ) - 1;

				$content 	= 'C' . substr( substr( $content , $start ) , 1 );

			}

		}

		//var_dump('org : '.$org);

		//$result['originalHtml'] 	= $org;
		//$result['html']				= $content;

		//Cache::write( $sha , $result );

		return $org;

	}

}
?>