<?php

/**
 * Redirect the user when the form is submitted
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-form.php');

class plgFabrik_FormRedirect extends plgFabrik_Form {

	var $_result = true;

	/**
	 * process the plugin, called afer form is submitted
	 *
	 * @param	object	$params (with the current active plugin values in them)
	 * @param	object	form model
	 */

	function onLastProcess($params, &$formModel)
	{
		$session = JFactory::getSession();
		$context = $formModel->getRedirectContext();
		//get existing session params
		$surl = (array)$session->get($context . 'url', array());
		$stitle = (array)$session->get($context . 'title', array());
		$smsg = (array)$session->get($context . 'msg', array());
		$sshowsystemmsg = (array)$session->get($context . 'showsystemmsg', array());

		$app = JFactory::getApplication();
		$this->formModel = $formModel;
		$w = new FabrikWorker();
		$this->_data = new stdClass();

		$this->_data->append_jump_url = $params->get('append_jump_url');
		$this->_data->save_in_session = $params->get('save_insession');
		$form = $formModel->getForm();

		// $$$ hugh - think we need to switcheroonie the order, otherwise _formData takes
		// precedence over getEmailData(), which I think kind of defeats the object of
		// the exercisee?
		$this->data = array_merge($this->getEmailData(), $formModel->_formData);
		$this->data = array_merge($formModel->_formData, $this->getEmailData());
		$this->_data->jump_page = $w->parseMessageForPlaceHolder($params->get('jump_page'), $this->data);
		$this->_data->thanks_message = $w->parseMessageForPlaceHolder($params->get('thanks_message'), $this->data);
		if (!$this->shouldRedirect($params))
		{
			//clear any sessoin redirects
			unset($surl[$this->renderOrder]);
			unset($stitle[$this->renderOrder]);
			unset($smsg[$this->renderOrder]);
			unset($sshowsystemmsg[$this->renderOrder]);

			$session->set($context . 'url', $surl);
			$session->set($context . 'title', $stitle);
			$session->set($context . 'msg', $smsg);
			$session->set($context . 'showsystemmsg', $sshowsystemmsg);
			return true;
		}
		$this->_storeInSession($formModel);
		$sshowsystemmsg[$this->renderOrder] = true;
		$session->set($context . 'showsystemmsg', $sshowsystemmsg);
		if ($this->_data->jump_page != '')
		{
			$this->_data->jump_page = $this->buildJumpPage($formModel);
			//3.0 ajax/module redirect logic handled in form controller not in plugin
			$surl[$this->renderOrder] = $this->_data->jump_page;
			$session->set($context.'url', $surl);
			$session->set($context.'redirect_content_how', $params->get('redirect_content_how', 'popup'));
			$session->set($context.'redirect_content_popup_width', $params->get('redirect_content_popup_width', '300'));
			$session->set($context.'redirect_content_popup_height', $params->get('redirect_content_popup_height', '300'));
			$session->set($context.'redirect_content_popup_x_offset', $params->get('redirect_content_popup_x_offset', '0'));
			$session->set($context.'redirect_content_popup_y_offset', $params->get('redirect_content_popup_y_offset', '0'));
			$session->set($context.'redirect_content_popup_title', $params->get('redirect_content_popup_title', ''));
			$session->set($context.'redirect_content_popup_reset_form', $params->get('redirect_content_popup_reset_form', '1'));
		}
		else
		{
			$sshowsystemmsg[$this->renderOrder] = false;
			$session->set($context . 'showsystemmsg', $sshowsystemmsg);

			$stitle[$this->renderOrder] = $form->label;
			$session->set($context . 'title', $stitle);

			$surl[$this->renderOrder] = 'index.php?option=com_fabrik&view=plugin&g=form&plugin=redirect&method=displayThanks&task=pluginAjax';
			$session->set($context.'url', $surl);
		}

		$smsg[$this->renderOrder] = $this->_data->thanks_message;
		$session->set($context . 'msg', $smsg);
		return true;
	}

	/**
	 * since 3.0 - called via ajax
	 */

	public function onDisplayThanks()
	{
		$this->displayThanks();
	}

	/**
	 * once the form has been sucessfully completed, and if no jump page is
	 * specified then show the thanks message
	 * @param string thanks message title @depreicated - set in session in onLastProcess
	 * @param string thanks message string @depreicated - set in session in onLastProcess
	 */

	function displayThanks($title = '', $message = '')
	{
		$session = JFactory::getSession();
		$formdata = $session->get('com_fabrik.form.data');
		$context = 'com_fabrik.form.'.$formdata['formid'].'.redirect.';
		$title = (array)$session->get($context.'title', $title);
		$title = array_shift($title);
		$message = $session->get($context.'msg', $message);
		if (JRequest::getVar('fabrik_ajax')) {
			//3.0 - standardize on msg/title options.
			$opts = new stdClass();
			$opts->title = $title;
			$opts->msg = $message;
			echo json_encode($opts);
		} else {
			// $$$ hugh - it's an array, need to bust it up.
			if (is_array($message)) {
				$message = implode('<br />', $message);
			}
			?>
<div class="componentheading"><?php echo $title ?></div>
<p><?php echo $message ?></p>
			<?php
		}
	}

	/**
	 * alter the returned plugin manager's result
	 *
	 * @param string $method
	 * @param object form model
	 * @return bol
	 */

	function customProcessResult($method, &$formModel)
	{
		// if we are applying the form don't run redirect
		if (is_array($formModel->_formData) && array_key_exists('apply', $formModel->_formData)) {
			return true;
		}
		if ($method != 'onLastProcess') {
			return true;
		}
		if (JRequest::getVar('fabrik_ajax')) {
			//return false to stop the default redirect occurring
			return false;
		} else {
			if (!empty($this->_data->jump_page)) {
				//ajax form submit load redirect page in mocha window
				if (strstr($this->_data->jump_page, "?")) {
					$this->_data->jump_page .= "&tmpl=component";
				} else {
					$this->_data->jump_page .= "?tmpl=component";
				}
				return false;
			}
			else {
				return true;
			}
		}
	}

	/**
	 * takes the forms data and merges it with the jump page
	 * @param object form
	 * @return new jump page
	 */

	protected function buildJumpPage(&$formModel)
	{
		///$$$rob - I've tested the issue reported in rev 1268
		//where Hugh added a force call to getTable() in elementModel->getFullName() to stop the wrong table name
		//being appended to the element name. But I can't reproduce the issue (Testing locally php 5.2.6 on my Gigs table)
		// if there is still an issue it would make a lot more sense to manually set the element's table model rather than calling
		//force in the getFullName() code - as doing so increases the table query count by a magnitude of 2
		$jumpPage = $this->_data->jump_page;
		$reserved = array('format','view','layout','task');
		$queryvars = array();
		if ($this->_data->append_jump_url == '1') {
			$groups = $formModel->getGroupsHiarachy();
			foreach ($groups as $group) {
				$elements = $group->getPublishedElements();
				if ($group->isJoin()) {
					$tmpData = $formModel->_fullFormData['join'][$group->getGroup()->join_id];
				} else {
					$tmpData = $formModel->_fullFormData;
				}
				foreach ($elements as $elementModel) {

					$name = $elementModel->getFullName(false, true, false);
					if (array_key_exists($name, $tmpData)) {
						$this->_appendQS($queryvars, $name, $tmpData[$name]);
					} else {
						$element = $elementModel->getElement();
						if (array_key_exists($element->name, $tmpData)) {
							$this->_appendQS($queryvars, $element->name, $tmpData[$element->name]);
						}
					}
				}
			}
		}

		// $$$ rob removed url comparison as this stopped form js vars being appeneded to none J site urls (e.g. http://google.com)
		//if ((!strstr($jumpPage, COM_FABRIK_LIVESITE) && strstr($jumpPage, 'http')) || empty($queryvars)) {
		if (empty($queryvars)) {
			return $jumpPage;
		}
		$jumpPage .= (!strstr($jumpPage, "?")) ? "?" : "&";
		$jumpPage .= implode('&', $queryvars);
		return $jumpPage;
	}

	function _appendQS(&$queryvars, $key, $val)
	{
		if (is_array($val)) {
			foreach ($val as $v) {
				$this->_appendQS($queryvars, "{$key}[value]", $v);
			}
		} else {
			$val = urlencode(stripslashes($val));
			$queryvars[] = "$key=$val";
		}
	}

	/**
	 * date is stored in session com_fabrik.searchform.form'.$formModel->get('id').'.filters
	 * listfilters looks up the com_fabrik.searchform.fromForm session var to then be able to pick up
	 * the search form data
	 * once its got it it unsets com_fabrik.searchform.fromForm so that the search values are not reused
	 * (they are however stored in the session so behave like normal filters afterwards)
	 * If the listfilter does find the com_fabrik.searchform.fromForm var it won't use any session filters
	 *
	 * @param $formModel
	 * @return unknown_type
	 */
	function _storeInSession(&$formModel)
	{
		$app = JFactory::getApplication();
		$store = array();
		if ($this->_data->save_in_session == '1') {
			//@TODO - rob, you need to look at this, I really only put this in as a band-aid.
			// $$$ hugh - we need to guesstimate the 'type', otherwise when the session data is processed
			// on table load as filters, everything will default to 'field', which borks up if (say) it's
			// really a dropdown
			/*
			 foreach ($formModel->_formData as $key => $value) {
				if ($formModel->hasElement($key)) {
				//$value = urlencode( stripslashes($value));
				$store[$formModel->get('id')]["$key"] = array('type'=>'', 'value'=>$value, 'match'=>false);
				}
				}
				*/

			$groups = $formModel->getGroupsHiarachy();
			foreach ($groups as $group) {
				$elements = $group->getPublishedElements();
				foreach ($elements as $element) {

					if ($group->isJoin()) {
						$tmpData = $formModel->_fullFormData['join'][$group->getGroup()->join_id];
					} else {
						$tmpData = $formModel->_fullFormData;
					}
					if ($element->getElement()->name == 'fabrik_list_filter_all') {
						continue;
					}
					$name =  $element->getFullName(false);
					if (array_key_exists($name, $tmpData)) {
						$value = $tmpData[$name];

						$match = $element->getElement()->filter_exact_match;
						if (!is_array($value)) {
							$value = array($value);
						}

						$c = 0;
						foreach ($value as $v) {
							if (count($value) == 1 || $c == 0) {
								$join = 'AND';
								$grouped = false;
							} else {
								$join = 'OR';
								$grouped = true;
							}
							if ($v != '') {
								$store['join'][] = $join;
								$store['key'][] = FabrikString::safeColName($name);
								$store['condition'][] = '=';
								$store['search_type'][] =  'search';
								$store['access'][] = 0;
								$store['grouped_to_previous'][] = $grouped;
								$store['eval'][] = FABRIKFILTER_TEXT;
								$store['required'][] = false;
								$store['value'][] = $v;
								$store['full_words_only'][] = false;
								$store['match'][] = $match;
								$store['hidden'][] = 0;
								$store['elementid'][] = $element->getElement()->id;
							}

							$c ++;
						}
					}
				}
			}

			//clear registry search form entries
			$key = 'com_fabrik.searchform';

			$listModel = $formModel->getlistModel();
			//check for special fabrik_list_filter_all element!
			$searchAll = JRequest::getVar($listModel->getTable()->db_table_name . '___fabrik_list_filter_all');

			$app->setUserState('com_fabrik.searchform.form' . $formModel->get('id') . '.searchall', $searchAll);
			$app->setUserState($key, $id);

			$app->setUserState('com_fabrik.searchform.form' . $formModel->get('id') . '.filters', $store);
			$app->setUserState('com_fabrik.searchform.fromForm', $formModel->get('id'));

		}
	}

	/**
	 * determines if a condition has been set and decides if condition is matched
	 *
	 * @param object $params
	 * @return bol true if you should redirect, false ignores redirect
	 */

	function shouldRedirect(&$params)
	{
		// if we are applying the form dont run redirect
		if (array_key_exists('apply', $this->formModel->_formData)) {
			return false;
		}
		return $this->shouldProcess('redirect_conditon');
	}
}
?>