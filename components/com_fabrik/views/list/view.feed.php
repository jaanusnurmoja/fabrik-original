<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class FabrikViewList extends JView{


	function display()
	{
		$app = JFactory::getApplication();
		$Itemid	= $app->getMenu('site')->getActive()->id;
		$config	= JFactory::getConfig();
		$user = JFactory::getUser();
		$model = $this->getModel();
		$model->setOutPutFormat('feed');

		$document = JFactory::getDocument();
		$document->_itemTags = array();

		//Get the active menu item
		$usersConfig = JComponentHelper::getParams('com_fabrik');

		$table = $model->getTable();
		$model->render();
		$params = $model->getParams();

		if ($params->get('rss') == '0') {
			return '';
		}

		$formModel = $model->getFormModel();
		$form = $formModel->getForm();

		$aJoinsToThisKey = $model->getJoinsToThisKey();
		/* get headings */
		$aTableHeadings = array();
		$groupModels = $formModel->getGroupsHiarachy();
		foreach ($groupModels as $groupModel) {
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel) {
				$element = $elementModel->getElement();
				$elParams = $elementModel->getParams();

				//$elParams = new fabrikParams($oElement->attribs, $mosConfig_absolute_path . '/administrator/components/com_fabrik/xml/element.xml', 'component');
				if ($elParams->get('show_in_rss_feed') == '1') {
					$heading = $element->label;
					if ($elParams->get('show_label_in_rss_feed') == '1') {
						$aTableHeadings[$heading]['label']	 = $heading;
					} else {
						$aTableHeadings[$heading]['label']	 = '';
					}
					$aTableHeadings[$heading]['colName'] = $elementModel->getFullName(false, true);
					$aTableHeadings[$heading]['dbField'] = $element->name;
					$aTableHeadings[$heading]['key'] = $elParams->get('use_as_fake_key');
				}
			}
		}

		foreach ($aJoinsToThisKey as $element) {
			$element = $elementModel->getElement();
			$elParams = new fabrikParams($element->attribs, JPATH_SITE . '/administrator/components/com_fabrik/xml/element.xml', 'component');
			if ($elParams->get('show_in_rss_feed') == '1') {
				$heading = $element->label;

				if ($elParams->get('show_label_in_rss_feed') == '1') {
					$aTableHeadings[$heading]['label']	 = $heading;
				} else {
					$aTableHeadings[$heading]['label']	 = '';
				}
				$aTableHeadings[$heading]['colName'] = $element->db_table_name . "___" . $element->name;
				$aTableHeadings[$heading]['dbField'] = $element->name;
				$aTableHeadings[$heading]['key'] = $elParams->get('use_as_fake_key');
			}
		}

		$dateCol = $params->get('feed_date', '');
		$w = new FabrikWorker();
		$rows = $model->getData();
		$document->title = $w->parseMessageForPlaceHolder($table->label, $_REQUEST);
		$document->description = htmlspecialchars(trim(strip_tags($w->parseMessageForPlaceHolder($table->introduction, $_REQUEST))));
		$document->link = JRoute::_('index.php?option=com_fabrik&view=list&listid='.$table->id.'&Itemid='.$Itemid);

		/* check for a custom css file and include it if it exists*/
		$tmpl = JRequest::getVar('layout', $table->template);
		$csspath = COM_FABRIK_FRONTEND.DS."views".DS."list".DS."tmpl".DS.$tmpl.DS.'feed.css';

		if (file_exists($csspath)) {
			$document->addStyleSheet(COM_FABRIK_LIVESITE . "components/com_fabrik/views/list/tmpl/$tmpl/feed.css");
		}

		$titleEl = $params->get('feed_title');
		$dateEl = $params->get('feed_date');
		$dateEl = $params->get('feed_date');
		$view = $model->canEdit() ? 'form' : 'details';

		//list of tags to look for in the row data
		//- if they are there don't put them in the desc but put them in as a seperate item param
		$rsstags = array('<georss:point>' => 'xmlns:georss="http://www.georss.org/georss"');
		foreach ($rows as $group)
		{
			foreach ($group as $row)
			{
				// strip html from feed item title
				//$title = html_entity_decode($this->escape( $row->$titleEl));

				//get the content
				$str2 = '';
				$str = '<table style="margin-top:10px;padding-top:10px;">';
				//used for content not in dl
				//ok for feed gator you cant have the same item title so we'll take the first value from the table (asume its the pk and use that to append to the item title)'
				$title = '';
				$item = new JFeedItem();

				foreach ($aTableHeadings as $heading=>$dbcolname) {
					if ($title == '') {
						//set a default title
						$title = $row->$dbcolname['colName'];
					}
					$rsscontent = $row->$dbcolname['colName'];

					$found = false;
					foreach($rsstags as $rsstag =>$namespace) {

						if (strstr($rsscontent, $rsstag)) {
							$found = true;
							if (!strstr($document->_namespace, $namespace)) {
								$rsstag = substr($rsstag, 1, strlen($rsstag)-2);
								$document->_itemTags[] = $rsstag;
								$document->_namespace .=  $namespace . "\n";
							}
							break;
						}
					}

					if ($found) {
						$item->{$rsstag} = $rsscontent;
					} else {
						if ($dbcolname['label'] == '') {
							$str2 .= $rsscontent . "<br />\n";
						} else {
							$str .= "<tr><td>".$dbcolname['label'].":</td><td>".$rsscontent."</td></tr>\n";
						}
					}
				}

				if (isset($row->$titleEl)) {
					$title = $row->$titleEl;
				}
				$str = $str2 . $str . "</table>";

				// url link to article
				$link = JRoute::_('index.php?option=com_fabrik&view='.$view.'&listid='.$table->id.'&formid='.$form->id.'&rowid='. $row->__pk_val);

				// strip html from feed item description text
				$author	= @$row->created_by_alias ? @$row->created_by_alias : @$row->author;

				if ($dateEl != '') {
					$date = ($row->$dateEl ? date('r', strtotime(@$row->$dateEl) ) : '');
				} else {
					$data = '';
				}
				// load individual item creator class

				$item->title = $title;
				$item->link = $link;
				$item->guid = $link;
				$item->description = $str;
				$item->date = $date;
				$item->category = $row->category;

				// loads item info into rss array
				$document->addItem($item);
			}
		}
	}

}
?>