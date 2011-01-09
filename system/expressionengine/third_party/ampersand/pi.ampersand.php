<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name'					=> "Ampersandwich",
	'pi_version'			=> "2.0",
	'pi_author'				=> "Scott Boms",
	'pi_author_url'		=> "http://scottboms.com",
	'pi_description'	=> "Improves the display of ampersands by wrapping them with "
											."<abbr title='and'></abbr> which you can then target using "
											."a CSS attribute selector.",
	'pi_usage'				=> Ampersand::usage()
);

/**
 * Ampersand Class
 * @package		ampersandwich_ee2_addon
 * @category	Plugin
 * @author		Scott Boms
 * @copyright	Copyright (c) Scott Boms
 * @link			http://scottboms.com
 */

class Ampersand {

 /**
	* Plugin return data
	* @access public
	* @var  string
	*/
	var $return_data = '';

 /**
	* PHP 4 Constructor
	* @see __construct()
	*/
	function Ampersand($str = '') {
		$this->__construct($str);
	}

 /**
	* PHP 5 Constructor
	* @param  string $str String to apply the ampersandwich algorithm to
	* @return string
	*/
	function __construct($str = '') {
		$this->EE =& get_instance();

		// Fetch the string
		if($str == '') {
			$str = $this->EE->TMPL->tagdata;
			$str = $this->wich($str);
			return $this->return_data;
		}
	}
	// END

	/** --------------------------------------
	/** Return the ampersandwich-ized string
	/** --------------------------------------*/
	function wich($str = '') {
		return $this->_apply_search_replace($str, '/(\s|&nbsp;)(&|&amp;|&\#38;)(\s|&nbsp;)/','\1<abbr title="and">&amp;</abbr>\3');
	}

	/** --------------------------------------
	/** Find and replace
	/** --------------------------------------*/
	function _apply_search_replace($str = '', $search, $replace) {
		if ($str == '') {
			$str = $this->EE->TMPL->tagdata;
		}

		$tokens = $this->_TokenizeHTML($str);
		$result = '';
		$in_skipped_tag = false;
	
		foreach ($tokens as $token) {
			if ($token[0] == 'tag') {
				$result .= $token[1];
				if (preg_match('_' . '_', $token[1], $matches))
					$in_skipped_tag = isset($matches[1]) && $matches[1] == '/' ? false : true;
			} else {
				if ($in_skipped_tag)
					$result .= $token[1];
				else
					$result .= preg_replace($search, $replace, $token[1]);
			}
		}
		return $result;
	}
	// END

	/** --------------------------------------
	/** Tokenize HTML elements
	/** --------------------------------------*/
	function _TokenizeHTML($str) {
	/**
		*	Parameter:	String containing HTML markup.
		*	Returns:		An array of the tokens comprising the input
		* 						string. Each token is either a tag (possibly with nested,
		* 						tags contained therein, such as <a href="<MTFoo>">, or a
		* 						run of text between tags. Each element of the array is a
		* 						two-element array; the first is either 'tag' or 'text';
		* 						the second is the actual value.
		* 
		*	Regular expression derived from the _tokenize() subroutine in 
		*	Brad Choate's MTRegex plugin.
		*	<http://www.bradchoate.com/past/mtregex.php>
		*/
		$index = 0;
		$tokens = array();

		$match = '(?s:<!(?:--.*?--\s*)+>)|'. // comment
			'(?s:<\?.*?\?>)|'. // processing instruction
			// regular tags
			'(?:<[/!$]?[-a-zA-Z0-9:]+\b(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*>)'; 

		$parts = preg_split("{($match)}", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

		foreach ($parts as $part) {
			if (++$index % 2 && $part != '') 
				$tokens[] = array('text', $part);
			else
				$tokens[] = array('tag', $part);
		}
		return $tokens;
	}
	// END

	/** --------------------------------------
	/** Plugin usage
	/** --------------------------------------*/
	function usage() {
		ob_start();
?>
One of the guidelines from the seminal book on typography by Robert Bringhurt, 
"The Elements of Typographic Style" states that "In heads and titles, use the 
best ampersand available". This plugin attempts to allow you to do just 
that with your text in ExpressionEngine 2.

To use this plugin, wrap anything you want to be processed by it between the 
following tag pairs. Entry titles or headline text works best though it should 
be able to handle most things you can throw at it.

{exp:ampersand:wich}{title}{/exp:ampersand:wich}

<?php
	$buffer = ob_get_contents();
	ob_end_clean();
	return $buffer;
	}
	// END
}
// END CLASS
?>