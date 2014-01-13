<?php
namespace Azera\Util;

class String
{

	/**
	 * 	@param 	$input 		=> 	Azera.Acl.User
	 * 	@param 	$var 		=>  Object | Model | Controller | Component | ...
	 *	@return array
	 */
	public static function dispatch( $input , $var 	= 'object' )
	{
        $input  = str_replace( '/' , '.' , $input );      // convert Module/Model to Module.Model
        $p      = explode( '.' , $input );
        $len    = count($p);
        list( ${$var} , $module , $bundle )  = array_reverse( $p );

        $route      = compact('bundle','module', $var );

        return $route;
	}

	/**
	 * Generate Namespace from dispatched or path
	 * @param $path 		=> Azera.Acl.User 	or Array
	 * @return string
	 */
	public static function className( $path , $type = 'Object' )
	{
		if ( is_string( $path ) )
			$path 	= self::dispatch( $path , $type );
		if ( is_object($path) )
			$path 	= (array)$path;

		$bundle 	= current($path);
		$module 	= next($path);
		unset($path['bundle'],$path['module']);
		$type 		= current(array_keys( $path ));
		$object 	= current( $path );

		if ( empty($bundle) && empty($module) )
		{
			return "App\\{$type}\\{$object}";
		}elseif ( $module == 'System' )
		{
			return "Azera\System\\{$type}\\{$object}";
		}

		return str_replace('\\\\','\\',"Bundle\\{$bundle}\\{$module}\\{$type}\\{$object}");


	}

	
	/**
	 * Convert User to Users ( plural string )
	 */
	public static function plural( $str )
	{
		if ( substr( $str , -1 , 1 ) == 'y' )
			return substr( $str , 0 , strlen( $str ) - 1 ) . 'es';
		if ( substr( $str , -1 , 1 ) == 's' )
			return $str . 'es';
		return $str . 's';
	}

	/**
	 * Convert HelloWorld to hello_world
	 */
	public static function underscore( $string )
	{
		$temp = '';
		$len  = strlen($string);
		for ( $i = 0 ; $i < $len ; $i++ )
		{
			if ( $i > 0 && String::upper(substr($string,$i,1)) == substr($string, $i,1) )
				$temp .= '_';
			$temp .= String::lower( substr($string,$i,1) );			
		}
		return $temp;
	}

	/**
	 * Strips image tags from output
	 *
	 * @param string $str String to sanitize
	 * @return string Sting with images stripped.
	 */
    public static function stripImages($str) {
        
        $preg = array(
                        '/(<a[^>]*>)(<img[^>]+alt=")([^"]*)("[^>]*>)(<\/a>)/i' => '$1$3$5<br />',
                        '/(<img[^>]+alt=")([^"]*)("[^>]*>)/i' => '$2<br />',
                        '/<img[^>]*>/i' => ''
        );

    	return preg_replace(array_keys($preg), array_values($preg), $str);
    }

	/**
	 * Strips scripts and stylesheets from output
	 *
	 * @param string $str String to sanitize
	 * @return string String with <link>, <img>, <script>, <style> elements and html comments removed.
	 */
    public static function stripScripts($str) {
                $regex =
                        '/(<link[^>]+rel="[^"]*stylesheet"[^>]*>|' .
                        '<img[^>]*>|style="[^"]*")|' .
                        '<script[^>]*>.*?<\/script>|' .
                        '<style[^>]*>.*?<\/style>|' .
                        '<!--.*?-->/is';
                return preg_replace($regex, '', $str);
    }

	/**
	 * Strips the specified tags from output. First parameter is string from
	 * where to remove tags. All subsequent parameters are tags.
	 *
	 * Ex.`$clean = Sanitize::stripTags($dirty, 'b', 'p', 'div');`
	 *
	 * Will remove all `<b>`, `<p>`, and `<div>` tags from the $dirty string.
	 *
	 * @param string $str,... String to sanitize
	 * @return string sanitized String
	 */
    public static function stripTags($str) {
                $params = func_get_args();

                for ($i = 1, $count = count($params); $i < $count; $i++) {
                        $str = preg_replace('/<' . $params[$i] . '\b[^>]*>/i', '', $str);
                        $str = preg_replace('/<\/' . $params[$i] . '[^>]*>/i', '', $str);
                }
                return $str;
    }

	/**
	 * Generate a random UUID
	 *
	 * @see http://www.ietf.org/rfc/rfc4122.txt
	 * @return RFC 4122 UUID
	 */
	public static function uuid() {
		$node = env('SERVER_ADDR');

		if (strpos($node, ':') !== false) {
			if (substr_count($node, '::')) {
				$node = str_replace(
					'::', str_repeat(':0000', 8 - substr_count($node, ':')) . ':', $node
				);
			}
			$node = explode(':', $node);
			$ipSix = '';

			foreach ($node as $id) {
				$ipSix .= str_pad(base_convert($id, 16, 2), 16, 0, STR_PAD_LEFT);
			}
			$node = base_convert($ipSix, 2, 10);

			if (strlen($node) < 38) {
				$node = null;
			} else {
				$node = crc32($node);
			}
		} elseif (empty($node)) {
			$host = env('HOSTNAME');

			if (empty($host)) {
				$host = env('HOST');
			}

			if (!empty($host)) {
				$ip = gethostbyname($host);

				if ($ip === $host) {
					$node = crc32($host);
				} else {
					$node = ip2long($ip);
				}
			}
		} elseif ($node !== '127.0.0.1') {
			$node = ip2long($node);
		} else {
			$node = null;
		}

		if (empty($node)) {
			$node = crc32(Configure::read('Security.salt'));
		}

		if (function_exists('hphp_get_thread_id')) {
			$pid = hphp_get_thread_id();
		} elseif (function_exists('zend_thread_id')) {
			$pid = zend_thread_id();
		} else {
			$pid = getmypid();
		}

		if (!$pid || $pid > 65535) {
			$pid = mt_rand(0, 0xfff) | 0x4000;
		}

		list($timeMid, $timeLow) = explode(' ', microtime());
		$uuid = sprintf(
			"%08x-%04x-%04x-%02x%02x-%04x%08x", (int)$timeLow, (int)substr($timeMid, 2) & 0xffff,
			mt_rand(0, 0xfff) | 0x4000, mt_rand(0, 0x3f) | 0x80, mt_rand(0, 0xff), $pid, $node
		);

		return $uuid;
	}

	/**
	 * Tokenizes a string using $separator, ignoring any instance of $separator that appears between
	 * $leftBound and $rightBound
	 *
	 * @param string $data The data to tokenize
	 * @param string $separator The token to split the data on.
	 * @param string $leftBound The left boundary to ignore separators in.
	 * @param string $rightBound The right boundary to ignore separators in.
	 * @return array Array of tokens in $data.
	 */
	public static function tokenize($data, $separator = ',', $leftBound = '(', $rightBound = ')') {
		if (empty($data) || is_array($data)) {
			return $data;
		}

		$depth = 0;
		$offset = 0;
		$buffer = '';
		$results = array();
		$length = strlen($data);
		$open = false;

		while ($offset <= $length) {
			$tmpOffset = -1;
			$offsets = array(
				strpos($data, $separator, $offset),
				strpos($data, $leftBound, $offset),
				strpos($data, $rightBound, $offset)
			);
			for ($i = 0; $i < 3; $i++) {
				if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset == -1)) {
					$tmpOffset = $offsets[$i];
				}
			}
			if ($tmpOffset !== -1) {
				$buffer .= substr($data, $offset, ($tmpOffset - $offset));
				if ($data{$tmpOffset} == $separator && $depth == 0) {
					$results[] = $buffer;
					$buffer = '';
				} else {
					$buffer .= $data{$tmpOffset};
				}
				if ($leftBound != $rightBound) {
					if ($data{$tmpOffset} == $leftBound) {
						$depth++;
					}
					if ($data{$tmpOffset} == $rightBound) {
						$depth--;
					}
				} else {
					if ($data{$tmpOffset} == $leftBound) {
						if (!$open) {
							$depth++;
							$open = true;
						} else {
							$depth--;
							$open = false;
						}
					}
				}
				$offset = ++$tmpOffset;
			} else {
				$results[] = $buffer . substr($data, $offset);
				$offset = $length + 1;
			}
		}
		if (empty($results) && !empty($buffer)) {
			$results[] = $buffer;
		}

		if (!empty($results)) {
			$data = array_map('trim', $results);
		} else {
			$data = array();
		}
		return $data;
	}

	/**
	 * Replaces variable placeholders inside a $str with any given $data. Each key in the $data array
	 * corresponds to a variable placeholder name in $str.
	 * Example: `String::insert(':name is :age years old.', array('name' => 'Bob', '65'));`
	 * Returns: Bob is 65 years old.
	 *
	 * Available $options are:
	 *
	 * - before: The character or string in front of the name of the variable placeholder (Defaults to `:`)
	 * - after: The character or string after the name of the variable placeholder (Defaults to null)
	 * - escape: The character or string used to escape the before character / string (Defaults to `\`)
	 * - format: A regex to use for matching variable placeholders. Default is: `/(?<!\\)\:%s/`
	 *   (Overwrites before, after, breaks escape / clean)
	 * - clean: A boolean or array with instructions for String::cleanInsert
	 *
	 * @param string $str A string containing variable placeholders
	 * @param string $data A key => val array where each key stands for a placeholder variable name
	 *     to be replaced with val
	 * @param string $options An array of options, see description above
	 * @return string
	 */
	public static function insert($str, $data, $options = array()) {
		$defaults = array(
			'before' => ':', 'after' => null, 'escape' => '\\', 'format' => null, 'clean' => false
		);
		$options += $defaults;
		$format = $options['format'];
		$data = (array)$data;
		if (empty($data)) {
			return ($options['clean']) ? String::cleanInsert($str, $options) : $str;
		}

		if (!isset($format)) {
			$format = sprintf(
				'/(?<!%s)%s%%s%s/',
				preg_quote($options['escape'], '/'),
				str_replace('%', '%%', preg_quote($options['before'], '/')),
				str_replace('%', '%%', preg_quote($options['after'], '/'))
			);
		}

		if (strpos($str, '?') !== false && is_numeric(key($data))) {
			$offset = 0;
			while (($pos = strpos($str, '?', $offset)) !== false) {
				$val = array_shift($data);
				$offset = $pos + strlen($val);
				$str = substr_replace($str, $val, $pos, 1);
			}
			return ($options['clean']) ? String::cleanInsert($str, $options) : $str;
		} else {
			asort($data);

			$hashKeys = array();
			foreach ($data as $key => $value) {
				$hashKeys[] = crc32($key);
			}

			$tempData = array_combine(array_keys($data), array_values($hashKeys));
			krsort($tempData);
			foreach ($tempData as $key => $hashVal) {
				$key = sprintf($format, preg_quote($key, '/'));
				$str = preg_replace($key, $hashVal, $str);
			}
			$dataReplacements = array_combine($hashKeys, array_values($data));
			foreach ($dataReplacements as $tmpHash => $tmpValue) {
				$tmpValue = (is_array($tmpValue)) ? '' : $tmpValue;
				$str = str_replace($tmpHash, $tmpValue, $str);
			}
		}

		if (!isset($options['format']) && isset($options['before'])) {
			$str = str_replace($options['escape'] . $options['before'], $options['before'], $str);
		}
		return ($options['clean']) ? String::cleanInsert($str, $options) : $str;
	}

	/**
	 * Cleans up a String::insert() formatted string with given $options depending on the 'clean' key in
	 * $options. The default method used is text but html is also available. The goal of this function
	 * is to replace all whitespace and unneeded markup around placeholders that did not get replaced
	 * by String::insert().
	 *
	 * @param string $str
	 * @param string $options
	 * @return string
	 * @see String::insert()
	 */
	public static function cleanInsert($str, $options) {
		$clean = $options['clean'];
		if (!$clean) {
			return $str;
		}
		if ($clean === true) {
			$clean = array('method' => 'text');
		}
		if (!is_array($clean)) {
			$clean = array('method' => $options['clean']);
		}
		switch ($clean['method']) {
			case 'html':
				$clean = array_merge(array(
					'word' => '[\w,.]+',
					'andText' => true,
					'replacement' => '',
				), $clean);
				$kleenex = sprintf(
					'/[\s]*[a-z]+=(")(%s%s%s[\s]*)+\\1/i',
					preg_quote($options['before'], '/'),
					$clean['word'],
					preg_quote($options['after'], '/')
				);
				$str = preg_replace($kleenex, $clean['replacement'], $str);
				if ($clean['andText']) {
					$options['clean'] = array('method' => 'text');
					$str = String::cleanInsert($str, $options);
				}
				break;
			case 'text':
				$clean = array_merge(array(
					'word' => '[\w,.]+',
					'gap' => '[\s]*(?:(?:and|or)[\s]*)?',
					'replacement' => '',
				), $clean);

				$kleenex = sprintf(
					'/(%s%s%s%s|%s%s%s%s)/',
					preg_quote($options['before'], '/'),
					$clean['word'],
					preg_quote($options['after'], '/'),
					$clean['gap'],
					$clean['gap'],
					preg_quote($options['before'], '/'),
					$clean['word'],
					preg_quote($options['after'], '/')
				);
				$str = preg_replace($kleenex, $clean['replacement'], $str);
				break;
		}
		return $str;
	}	

	/**
	 * Wraps text to a specific width, can optionally wrap at word breaks.
	 *
	 * ### Options
	 *
	 * - `width` The width to wrap to.  Defaults to 72
	 * - `wordWrap` Only wrap on words breaks (spaces) Defaults to true.
	 * - `indent` String to indent with. Defaults to null.
	 * - `indentAt` 0 based index to start indenting at. Defaults to 0.
	 *
	 * @param string $text Text the text to format.
	 * @param array|integer $options Array of options to use, or an integer to wrap the text to.
	 * @return string Formatted text.
	 */
	public static function wrap($text, $options = array()) {
		if (is_numeric($options)) {
			$options = array('width' => $options);
		}
		$options += array('width' => 72, 'wordWrap' => true, 'indent' => null, 'indentAt' => 0);
		if ($options['wordWrap']) {
			$wrapped = wordwrap($text, $options['width'], "\n");
		} else {
			$wrapped = trim(chunk_split($text, $options['width'] - 1, "\n"));
		}
		if (!empty($options['indent'])) {
			$chunks = explode("\n", $wrapped);
			for ($i = $options['indentAt'], $len = count($chunks); $i < $len; $i++) {
				$chunks[$i] = $options['indent'] . $chunks[$i];
			}
			$wrapped = implode("\n", $chunks);
		}
		return $wrapped;
	}

	/**
	 * Highlights a given phrase in a text. You can specify any expression in highlighter that
	 * may include the \1 expression to include the $phrase found.
	 *
	 * ### Options:
	 *
	 * - `format` The piece of html with that the phrase will be highlighted
	 * - `html` If true, will ignore any HTML tags, ensuring that only the correct text is highlighted
	 * - `regex` a custom regex rule that is ued to match words, default is '|$tag|iu'
	 *
	 * @param string $text Text to search the phrase in
	 * @param string $phrase The phrase that will be searched
	 * @param array $options An array of html attributes and options.
	 * @return string The highlighted text
	 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/text.html#TextHelper::highlight
	 */
	public static function highlight($text, $phrase, $options = array()) {
		if (empty($phrase)) {
			return $text;
		}

		$default = array(
			'format' => '<span class="highlight">\1</span>',
			'html' => false,
			'regex' => "|%s|iu"
		);
		$options = array_merge($default, $options);
		extract($options);

		if (is_array($phrase)) {
			$replace = array();
			$with = array();

			foreach ($phrase as $key => $segment) {
				$segment = '(' . preg_quote($segment, '|') . ')';
				if ($html) {
					$segment = "(?![^<]+>)$segment(?![^<]+>)";
				}

				$with[] = (is_array($format)) ? $format[$key] : $format;
				$replace[] = sprintf($options['regex'], $segment);
			}

			return preg_replace($replace, $with, $text);
		} else {
			$phrase = '(' . preg_quote($phrase, '|') . ')';
			if ($html) {
				$phrase = "(?![^<]+>)$phrase(?![^<]+>)";
			}

			return preg_replace(sprintf($options['regex'], $phrase), $format, $text);
		}
	}

	/**
	 * Truncates text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ending if the text is longer than length.
	 *
	 * ### Options:
	 *
	 * - `ending` Will be used as Ending and appended to the trimmed string
	 * - `exact` If false, $text will not be cut mid-word
	 * - `html` If true, HTML tags would be handled correctly
	 *
	 * @param string $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param array $options An array of html attributes and options.
	 * @return string Trimmed string.
	 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/text.html#TextHelper::truncate
	 */
	public static function truncate($text, $length = 100, $options = array()) {
		$default = array(
			'ending' => '...', 'exact' => true, 'html' => false
		);
		$options = array_merge($default, $options);
		extract($options);

		if (!function_exists('mb_strlen')) {
			class_exists('Multibyte');
		}

		if ($html) {
			if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			$totalLength = mb_strlen(strip_tags($ending));
			$openTags = array();
			$truncate = '';

			preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
			foreach ($tags as $tag) {
				if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
					if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
						array_unshift($openTags, $tag[2]);
					} elseif (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
						$pos = array_search($closeTag[1], $openTags);
						if ($pos !== false) {
							array_splice($openTags, $pos, 1);
						}
					}
				}
				$truncate .= $tag[1];

				$contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
				if ($contentLength + $totalLength > $length) {
					$left = $length - $totalLength;
					$entitiesLength = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1] + 1 - $entitiesLength <= $left) {
								$left--;
								$entitiesLength += mb_strlen($entity[0]);
							} else {
								break;
							}
						}
					}

					$truncate .= mb_substr($tag[3], 0 , $left + $entitiesLength);
					break;
				} else {
					$truncate .= $tag[3];
					$totalLength += $contentLength;
				}
				if ($totalLength >= $length) {
					break;
				}
			}
		} else {
			if (mb_strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = mb_substr($text, 0, $length - mb_strlen($ending));
			}
		}
		if (!$exact) {
			$spacepos = mb_strrpos($truncate, ' ');
			if ($html) {
				$truncateCheck = mb_substr($truncate, 0, $spacepos);
				$lastOpenTag = mb_strrpos($truncateCheck, '<');
				$lastCloseTag = mb_strrpos($truncateCheck, '>');
				if ($lastOpenTag > $lastCloseTag) {
					preg_match_all('/<[\w]+[^>]*>/s', $truncate, $lastTagMatches);
					$lastTag = array_pop($lastTagMatches[0]);
					$spacepos = mb_strrpos($truncate, $lastTag) + mb_strlen($lastTag);
				}
				$bits = mb_substr($truncate, $spacepos);
				preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
				if (!empty($droppedTags)) {
					if (!empty($openTags)) {
						foreach ($droppedTags as $closingTag) {
							if (!in_array($closingTag[1], $openTags)) {
								array_unshift($openTags, $closingTag[1]);
							}
						}
					} else {
						foreach ($droppedTags as $closingTag) {
							array_push($openTags, $closingTag[1]);
						}
					}
				}
			}
			$truncate = mb_substr($truncate, 0, $spacepos);
		}
		$truncate .= $ending;

		if ($html) {
			foreach ($openTags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}

	/**
	 * Creates a comma separated list where the last two items are joined with 'and', forming natural English
	 *
	 * @param array $list The list to be joined
	 * @param string $and The word used to join the last and second last items together with. Defaults to 'and'
	 * @param string $separator The separator used to join all the other items together. Defaults to ', '
	 * @return string The glued together string.
	 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/text.html#TextHelper::toList
	 */
	public static function toList($list, $and = 'and', $separator = ', ') {
		if (count($list) > 1) {
			return implode($separator, array_slice($list, null, -1)) . ' ' . $and . ' ' . array_pop($list);
		} else {
			return array_pop($list);
		}
	}

	public static function startWith( $string , $start )
	{
		return ( substr( $string , 0 , strlen($start) ) == $start );
	}

	public static function Ab( $string )
	{
		$temp 	= String::split( $string , '_');
		foreach ( $temp as $i => $string )
			$temp[$i] = strtoupper( substr( $string , 0 , 1 ) ) . strtolower( substr( $string , 1 ) );
		return implode( ' ' , $temp );
	}

	public static function upper( $string )
	{
		return strtoupper($string);
	}

	public static function lower( $string )
	{
		return strtolower($string);
	}

	public static function indexOf( $string , $findMe )
	{
		return strpos( $string , $findMe );
	}

	public static function split( $string , $by )
	{
		return explode( $by , $string );
	}

	public static function replace( $string , $find , $replace = '' )
	{
		if ( is_array($find) )
			return strtr( $string ,$find );
		return str_replace($find, $replace, $string);
	}	
	
	public static function path( $path )
	{
	    return strtr( $path , array(
	               '\\'    => DS
	        ) );
	}

}
?>