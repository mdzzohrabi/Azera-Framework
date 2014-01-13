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
	function addTag( $tags = array() )
	{
		if ( is_array( $tags ) )
		{
			$this->reservedTags 	= array_merge( $this->reservedTags , $tags );
			return $this;
		}
		$this->reservedTags = array_merge( $this->reservedTags , func_get_args() );
		return $this;
	}

	function __construct( $content )
	{
		$this->orginalHtml 	= $content;
	}

	/**
	 * Parse Tags From Content
	 * @return Array
	 */
	public function parse()
	{

		$content 	= $this->orginalHtml;

		$sha 	= 'html_parsed_' . sha1( $content . implode('',$this->reservedTags));		// Generate Content Hash Checkshum

		/** Read Parsed Data From Cache **/
		if ( $result = Cache::read( $sha ) )
		{
		//	return (object)$result;
		}

		$result 	= array(
				'tags'	=> array(),			// Array Of Founded Tags
				'length'=> 0				// Number Of Tags
			);

		$i 			= 0;

		$org 		= $content;

		foreach ( $this->reservedTags as $tag) {
			
			while ( ($start = String::indexOf( $content , "<$tag " )) !== false || ($start = String::indexOf( $content , "<$tag/>" )) !== false || ($start = String::indexOf( $content , "<$tag>" )) !== false )
			{
				$i++;	# Tags Count Add

				$temp 	= explode( "<$tag" 	, $content , 2 ); 	 # Split Content By <Tag

				/* find end of tag */
				if ( strpos( $temp[1] , '/>' ) !== false && strpos( $temp[1] , "</$tag>" ) !== false )
				{
					$end 	= ( strpos( $temp[1] , '/>' ) < strpos( $temp[1] , "</$tag>" ) ? '/>' : "</$tag>" );
				}
				elseif ( strpos( $temp[1] , "</$tag>" ) )
				{
					$end 	= "</$tag>";
				}
				else
				{
					$end 	= '/>';
				}

				$tagType 	= ( $end == '/>' ? 'closed' : 'open' );

				$temp 	= explode( $end 	, $temp[1] , 2 );

				$inner 	= $temp[0];

				$tagHtml= "<$tag$inner$end";

				$length 	= strlen( $tagHtml );

				/* get tag attrs */
				$attrs 		= array();
				$values 	= array();
				$n 			= 0;
				$_tagHtml 	= explode( ( $tagType == 'closed' ? '/>' : '>') ,$inner);
				$_tagHtml 	= $_tagHtml[0];
				# take label to values
				while ( ( $start2 = strpos( $_tagHtml , '"' ) ) !== false )
				{
					$temp 	= explode('"' , $_tagHtml , 2);
					if ( strpos( $temp[1] , '"' ) === false ) break;
					$temp 	= explode('"' , $temp[1] , 2 );
					$value 	= $temp[0];
					$values[] 	= $value;
					$_tagHtml 	= substr( $_tagHtml , 0 , $start2 ) . '#'.++$n.'#' . substr( $_tagHtml , $start2 + strlen( $value ) + 2 );
				}
				$innerHtml 	= null;
				if ( $end != '/>' )
				{
					$innerHtml 	= explode( '>' , $inner );
					$innerHtml 	= $innerHtml[1];
				}
				$_tagHtml 	= trim(str_replace( '  ' , ' ' , $_tagHtml ));
				$attrs 		= explode( ' ' , $_tagHtml );
				$vc 		= 0;	// tags with value
				foreach ( $attrs as $j => $v )
				{
					unset( $attrs[$j] );
					if ( empty($v) ) continue;
					$key 	= explode('=' , $v);
					$key 	= $key[0];
					if ( strpos($v,'=') !== false )
						$attrs[ $key ] 	= $values[ $vc++ ];
					else
						$attrs[ $key ] 	= true;
				}

				$parts 	= explode( ':' , $tag , 2 );

				$result['tags'][] 	= (object)array(
						'label'		=> "::::tag#$i::::",
						'html'		=> $tagHtml,
						'attrs'		=> $attrs,
						'innerHtml'	=> $innerHtml,
						'tagType'	=> $tagType,
						'name'		=> $tag
					);

				$result['length']++;

				$content 	= substr( $content , 0 , $start ) . "::::tag#$i::::" . substr( $content , ( $start + $length ) );

			}

		}

		$result['originalHtml'] 	= $org;
		$result['html']				= $content;

		Cache::write( $sha , $result );

		return (object)$result;

	}

}
?>