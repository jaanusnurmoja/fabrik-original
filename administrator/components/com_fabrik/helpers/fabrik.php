<?php

/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Fabrik Component Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */
class FabrikHelper
{

	/**
	 * prepare the date for saving
	 * DATES SHOULD BE SAVED AS UTC
	 * @param string publish down date
	 */

	function prepareSaveDate(&$strdate)
	{
		$config =& JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		$db =& FabrikWorker::getDbo(true);
		// Handle never unpublish date
		if (trim($strdate) == JText::_('Never') || trim($strdate) == '' || trim($strdate) == $db->getNullDate())
		{
			$strdate = $db->getNullDate();
		}
		else
		{
			if (strlen(trim($strdate )) <= 10) {
				$strdate .= ' 00:00:00';
			}
			$date =& JFactory::getDate($strdate, $tzoffset);
			$strdate = $date->toMySQL();
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($categoryId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId)) {
			$assetName = 'com_fabrik';
		} else {
			$assetName = 'com_fabrik.category.'.(int)$categoryId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_LISTS'),
			'index.php?option=com_fabrik&view=lists',
			$vName == 'lists'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_FORMS'),
			'index.php?option=com_fabrik&view=forms',
			$vName == 'forms'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_GROUPS'),
			'index.php?option=com_fabrik&view=groups',
			$vName == 'groups'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_ELEMENTS'),
			'index.php?option=com_fabrik&view=elements',
			$vName == 'elements'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_VISUALIZATIONS'),
			'index.php?option=com_fabrik&view=visualizations',
			$vName == 'visualizations'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_PACKAGES'),
			'index.php?option=com_fabrik&view=packages',
			$vName == 'packages'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_CONNECTIONS'),
			'index.php?option=com_fabrik&view=connections',
			$vName == 'connections'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_CRONS'),
			'index.php?option=com_fabrik&view=crons',
			$vName == 'crons'
		);

	}
	
	/**
	* Applies the content tag filters to arbitrary text as per settings for current user group
	* @param text The string to filter
	* @return string The filtered string
	*/
	public static function filterText($text)
	{
		// Filter settings
		jimport('joomla.application.component.helper');
		$config = JComponentHelper::getParams('com_config');
		$user = JFactory::getUser();
		$userGroups	= JAccess::getGroupsByUser($user->get('id'));
	
		$filters = $config->get('filters');
	
		$blackListTags = array();
		$blackListAttributes = array();
	
		$whiteListTags = array();
		$whiteListAttributes = array();
	
		$noHtml = false;
		$whiteList = false;
		$blackList = false;
		$unfiltered	= false;
	
		// Cycle through each of the user groups the user is in.
		// Remember they are include in the Public group as well.
		foreach ($userGroups as $groupId)
		{
			// May have added a group by not saved the filters.
			if (!isset($filters->$groupId)) {
				continue;
			}
	
			// Each group the user is in could have different filtering properties.
			$filterData = $filters->$groupId;
			$filterType	= strtoupper($filterData->filter_type);
			if ($filterType == 'NH')
			{
				// Maximum HTML filtering.
				$noHtml = true;
			}
			elseif ($filterType == 'NONE')
			{
				// No HTML filtering.
				$unfiltered = true;
			}
			else
			{
				// Black or white list.
				// Preprocess the tags and attributes.
				$tags = explode(',', $filterData->filter_tags);
				$attributes = explode(',', $filterData->filter_attributes);
				$tempTags = array();
				$tempAttributes	= array();
	
				foreach ($tags as $tag)
				{
					$tag = trim($tag);
					if ($tag)
					{
						$tempTags[] = $tag;
					}
				}
				foreach ($attributes as $attribute)
				{
					$attribute = trim($attribute);
					if ($attribute)
					{
						$tempAttributes[] = $attribute;
					}
				}
	
				// Collect the black or white list tags and attributes.
				// Each list is cummulative.
				if ($filterType == 'BL')
				{
					$blackList = true;
					$blackListTags = array_merge($blackListTags, $tempTags);
					$blackListAttributes = array_merge($blackListAttributes, $tempAttributes);
				}
				elseif ($filterType == 'WL')
				{
					$whiteList = true;
					$whiteListTags = array_merge($whiteListTags, $tempTags);
					$whiteListAttributes = array_merge($whiteListAttributes, $tempAttributes);
				}
			}
		}
	
		// Remove duplicates before processing (because the black list uses both sets of arrays).
		$blackListTags = array_unique($blackListTags);
		$blackListAttributes = array_unique($blackListAttributes);
		$whiteListTags = array_unique($whiteListTags);
		$whiteListAttributes = array_unique($whiteListAttributes);
	
		// Unfiltered assumes first priority.
		if ($unfiltered)
		{
			// Dont apply filtering.
		}
		else
		{
			// Black lists take second precedence.
			if ($blackList)
			{
				// Remove the white-listed attributes from the black-list.
				$filter = JFilterInput::getInstance(
				array_diff($blackListTags, $whiteListTags), 			// blacklisted tags
				array_diff($blackListAttributes, $whiteListAttributes), // blacklisted attributes
				1,														// blacklist tags
				1														// blacklist attributes
				);
			}
			// White lists take third precedence.
			elseif ($whiteList)
			{
				$filter	= JFilterInput::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);  // turn off xss auto clean
			}
			// No HTML takes last place.
			else
			{
				$filter = JFilterInput::getInstance();
			}
			$text = $filter->clean($text, 'html');
		}
		return $text;
	}
}
?>