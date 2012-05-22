<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

/**
 * Renders a table of options for controlling the facet / related data links
 *
 * @author 		rob clayburn
 * @package 	fabrikar
 * @subpackage		Parameter
 * @since		1.6
 */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

class JFormFieldFactedlinks extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Factedlinks';

	function getInput()
	{
		$feListModel = $this->form->model->getFEModel();
		$joins = $feListModel->getJoinsToThisKey();
		if (empty($joins)) {
			return "<i>" . JText::_('COM_FABRIK_NO_RELATED_DATA') . "</i>";
		}
		$form = $this->form;
		$this->value = (array)$this->value;
		$linkedLists = JArrayHelper::getValue($this->value, 'linkedlist', array());
		$linkedForms 	= JArrayHelper::getValue($this->value, 'linkedform', array());
		$listHeaders = JArrayHelper::getValue($this->value, 'linkedlistheader', array());
		$formHeaders = JArrayHelper::getValue($this->value, 'linkedformheader', array());
		$formLinkTypes = JArrayHelper::getValue($this->value, 'linkedform_linktype', array());
		$listLinkTypes = JArrayHelper::getValue($this->value, 'linkedlist_linktype', array());
		$listLinkTexts = JArrayHelper::getValue($this->value, 'linkedlisttext', array());
		$formLinkTexts = JArrayHelper::getValue($this->value, 'linkedformtext', array());

		$this->linkedlists  = array();
		$f = 0;
		$listreturn = array();
		$formreturn = array();
		$listreturn[] = "<h4>".JText::_('COM_FABRIK_LISTS')."</h4><table class=\"adminlist linkedLists\">
					<thead>
					<tr>
						<th></th>
						<th>".JText::_('COM_FABRIK_LIST')."</th>
						<th>".JText::_('COM_FABRIK_LINK_TO_LIST')."</th>
						<th>".JText::_('COM_FABRIK_HEADING')."</th>
						<th>".JText::_('COM_FABRIK_BUTTON_TEXT')."</th>
						<th>".JText::_('COM_FABRIK_POPUP')."</th>
					</tr>
				</thead>
				<tbody>";
		$formreturn[] = "<h4>".JText::_('COM_FABRIK_FORMS')."</h4><table class=\"adminlist linkedForms\">
					<thead>
					<tr>
						<th></th>
						<th>".JText::_('COM_FABRIK_LIST')."</th>
						<th>".JText::_('COM_FABRIK_LINK_TO_FORM')."</th>
						<th>".JText::_('COM_FABRIK_HEADING')."</th>
						<th>".JText::_('COM_FABRIK_BUTTON_TEXT')."</th>
						<th>".JText::_('COM_FABRIK_POPUP')."</th>
					</tr>
				</thead>
				<tbody>";
		foreach ($joins as $linkedList) {
			$key = $linkedList->list_id.'-'.$linkedList->form_id.'-'.$linkedList->element_id;

			$label = str_replace(array("\n", "\r", "<br>", "</br>") , '', $linkedList->listlabel);
			$hover = JText::_('ELEMENT') . ': ' . $linkedList->element_label . " [$linkedList->plugin]";

			$listreturn[] = "<tr class=\"row".($f % 2)."\">";
			$listreturn[] = "<td class=\"handle\"></td>";
			$listreturn[] = "<td>".JHTML::_('tooltip', $hover, $label, 'tooltip.png', $label);

			$yeschecked = JArrayHelper::getValue($linkedLists, $key, 0) != '0' ? 'checked="checked"' : '';
			$nochecked = $yeschecked == '' ? 'checked="checked"' : '';

			$listreturn[] = "<td>";
			$listreturn[] = '<label><input name="'.$this->name.'[linkedlist][' . $key . ']" value="0" ' .$nochecked . ' type="radio" />' . JText::_('JNO') . '</label>';
			$listreturn[] = '<label><input name="'.$this->name.'[linkedlist][' . $key . ']" value="' . $key .'" ' .$yeschecked . ' type="radio" />' . JText::_('JYES') . '</label>';
			$listreturn[] = "</td>";

			$listreturn[] = "<td>";
			$listreturn[] = '<input name="'.$this->name.'[linkedlistheader][' . $key . ']" value="' . @$listHeaders[$key] .'" size="16" />';
			$listreturn[] = "</td>";

			$listreturn[] = "<td>";
			$listreturn[] = '<input name="'.$this->name.'[linkedlisttext][' . $key . ']" value="' . @$listLinkTexts[$key] .'" size="16" />';
			$listreturn[] = "</td>";

			$yeschecked = JArrayHelper::getValue($listLinkTypes, $key, 0) != '0' ? 'checked="checked"': '';
			$nochecked = $yeschecked == '' ? 'checked="checked"' : '';

			$listreturn[] = "<td>";
			$listreturn[] = '<label><input name="'.$this->name.'[linkedlist_linktype][' . $key . ']" value="0" ' .$nochecked . ' type="radio" />' . JText::_('JNO') . '</label>';
			$listreturn[] = '<label><input name="'.$this->name.'[linkedlist_linktype][' . $key . ']" value="' . $key .'" ' .$yeschecked . ' type="radio" />' . JText::_('JYES') . '</label>';
			$listreturn[] = "</td>";
			$listreturn[] = "</tr>";

			$yeschecked = JArrayHelper::getValue($linkedForms, $key, 0) != '0'? 'checked="checked"': '';
			$nochecked = $yeschecked == '' ? 'checked="checked"' : '';

			$formreturn[] = "<tr class=\"row".($f % 2)."\">";
			$formreturn[] = "<td class=\"handle\"></td>";
			$formreturn[] = "<td>".JHTML::_('tooltip', $hover, $label, 'tooltip.png', $label);
			$formreturn[] = "<td>";
			$formreturn[] = '<label><input name="'.$this->name.'[linkedform][' . $key . ']" value="0" ' .$nochecked . ' type="radio" />' . JText::_('JNO') . '</label>';
			$formreturn[] = '<label><input name="'.$this->name.'[linkedform][' . $key . ']" value="' . $key .'" ' .$yeschecked . ' type="radio" />' . JText::_('JYES') . '</label>';
			$formreturn[] = "</td>";

			$formreturn[] = "<td>";
			$formreturn[] = '<input name="'.$this->name.'[linkedformheader][' . $key . ']" value="' . @$formHeaders[$key] .'" size="16" />';
			$formreturn[] = "</td>";

			$formreturn[] = "<td>";
			$formreturn[] = '<input name="'.$this->name.'[linkedformtext][' . $key . ']" value="' . @$formLinkTexts[$key] .'" size="16" />';
			$formreturn[] = "</td>";

			$yeschecked = JArrayHelper::getValue($formLinkTypes, $key, 0) != '0' ? 'checked="checked"': '';
			$nochecked = $yeschecked == '' ? 'checked="checked"' : '';

			$formreturn[] = "<td>";
			$formreturn[] = '<label><input name="'.$this->name.'[linkedform_linktype][' . $key . ']" value="0" ' .$nochecked . ' type="radio" />' . JText::_('JNO') . '</label>';
			$formreturn[] = '<label><input name="'.$this->name.'[linkedform_linktype][' . $key . ']" value="' . $key .'" ' .$yeschecked . ' type="radio" />' . JText::_('JYES') . '</label>';
			$formreturn[] = "</td>";
			$formreturn[] = "</tr>";

			$f ++;
		}
		$listreturn[] = "</tbody></table>";
		$formreturn[] = "</tbody></table>";
		$return = array_merge($listreturn, $formreturn);
		return implode("\n", $return);
	}
}
?>