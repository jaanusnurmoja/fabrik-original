<?php
/**
 * Plugin element to render facebook open graph activity feed widget
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');

class plgFabrik_ElementFbactivityfeed extends plgFabrik_Element {

	var $hasLabel = false;

	protected $fieldDesc = 'INT(%s)';

	protected $fieldSize = '1';

	/**
	 * draws the form element
	 * @param array data to pre-populate element with
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$str = FabrikHelperHTML::facebookGraphAPI( $params->get('opengraph_applicationid'));
		$domain = $params->get('fbactivityfeed_domain');
		$width = $params->get('fbactivityfeed_width', 300);
		$height = $params->get('fbactivityfeed_height', 300);
		$header = $params->get('fbactivityfeed_header', 1) ? 'true' : 'false';
		$border = $params->get('fbactivityfeed_border', '');
		$font = $params->get('fbactivityfeed_font', 'arial');
		$colorscheme = $params->get('fbactivityfeed_colorscheme', 'light');
		$str .= "<fb:activity site=\"$domain\" width=\"$width\" height=\"$height\" header=\"$header\" colorscheme=\"$colorscheme\" font=\"$font\" border_color=\"$border\" />";
		return $str;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbActivityfeed('$id', $opts)";
	}

}
?>