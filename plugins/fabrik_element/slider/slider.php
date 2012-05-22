<?php
/**
 * Plugin element to render mootools slider
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');

class plgFabrik_ElementSlider extends plgFabrik_Element {

	protected $fieldDesc = 'INT(%s)';

	protected $fieldSize = '6';

	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData($data, $oAllRowsData)
	{
		$params = $this->getParams();

		return parent::renderListData($data, $oAllRowsData);
	}

	/**
	 * draws the form element
	 * @param array data to preopulate element with
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		FabrikHelperHTML::stylesheet(COM_FABRIK_LIVESITE.'media/com_fabrik/css/slider.css');
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$width = (int)$params->get('slider_width', 250);
		$element = $this->getElement();
		$val = $this->getValue($data, $repeatCounter);
		if (!$this->_editable) {
			return $val;
		}
		$labels = array_filter(explode(',', $params->get('slider-labels')));
		$str = array();
		$str[] = '<div id="' . $id . '" class="fabrikSubElementContainer">';

		FabrikHelperHTML::addPath(JPATH_SITE.DS.'plugins/fabrik_element/slider/images/', 'image', 'form', false);
		$outsrc = FabrikHelperHTML::image('clear_rating_out.png', 'form', $this->tmpl, array(), true);
		if ($params->get('slider-shownone')) {
			$str[] = '<div class="clearslider_cont"><img src="'.$outsrc.'" style="cursor:pointer;padding:3px;" alt="'.JText::_('PLG_ELEMENT_SLIDER_CLEAR').'" class="clearslider" /></div>';
		}
		$str[] = '<div class="slider_cont" style="width:'.$width.'px;">';
		if (count($labels) > 0) {
			$spanwidth = floor(($width - (2 * count($labels))) /count($labels));
			$str[] = '<ul class="slider-labels" style="width:'.$width.'px;">';
			for ($i=0; $i < count($labels); $i++) {
				if ($i == ceil(floor($labels)/2)) {
					$align = 'center';
				}
				switch($i) {
					case 0:
						$align = 'left';
						break;
					case 1:
					default:
						$align = 'center';
						break;
					case count($labels) -1:
						$align = 'right';
						break;
				}
				$str[] = '<li style="width:'.$spanwidth.'px;text-align:'.$align.';">'.$labels[$i].'</li>';
			}
			$str[] = '</ul>';
		}
		$str[] = '<div class="fabrikslider-line" style="width:'.$width.'px">';
		$str[] = '<div class="knob"></div>';
		$str[] = '</div>';
		$str[] = '<input type="hidden" class="fabrikinput" name="'.$name.'" value="'.$val.'" />';
		$str[] = '<div class="slider_output">'.$val.'</div>';
		$str[] = '</div>';
		$str[] = '</div>';
		return implode("\n", $str);
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param	int		repeat counter
	 * @return string 	javascript to create instance
	 */

	function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->steps = (int)$params->get('slider-steps', 100);
		$data = $this->_form->_data;
		$opts->value = $this->getValue($data, $repeatCounter);
		$opts = json_encode($opts);
		return "new FbSlider('$id', $opts)";
	}
	
}
?>