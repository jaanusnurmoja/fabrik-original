<?php
/**
 * Send a receipt
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

class plgFabrik_FormReceipt extends plgFabrik_Form {

	var $html = null;

	/**
	 * set up the html to be injected into the bottom of the form
	 *
	 * @param object $params (no repeat counter stuff needed here as the plugin manager
	 * which calls this function has already done the work for you
	 */

	function getBottomContent(&$params)
	{
		if($params->get('ask-receipt')) {
			$this->html = "
			<label><input type=\"checkbox\" name=\"fabrik_email_copy\" class=\"contact_email_copy\" value=\"1\"  />
			 ".JText::_('PLG_FORM_RECEIPT_EMAIL_ME_A_COPY') . "</label>";
		}else{
			$this->html = '';
		}
	}

	/**
	 * inject custom html into the bottom of the form
	 * @param int plugin counter
	 * @return string html
	 */

	function getBottomContent_result($c)
	{
		return $this->html;
	}

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param object $params
	 * @param object form
	 */

	function onAfterProcess($params, &$formModel)
	{
		if ($params->get('ask-receipt')) {
			$post = JRequest::get('post');
			if (!array_key_exists('fabrik_email_copy', $post)) {
				return;
			}
		}
		$config = JFactory::getConfig();
		$w = new FabrikWorker();

		$this->formModel = $formModel;
		$form = $formModel->getForm();

		//getEmailData returns correctly formatted {tablename___elementname} keyed results
		//_formData is there for legacy and may allow you to use {elementname} only placeholders for simple forms
		$aData 		= array_merge($this->getEmailData(), $formModel->_formData);

		$message = $params->get('receipt_message');
		$editURL = COM_FABRIK_LIVESITE . "index.php?option=com_fabrik&amp;view=form&amp;fabrik=".$formModel->get('id')."&amp;rowid=".JRequest::getVar('rowid');
		$viewURL = COM_FABRIK_LIVESITE . "index.php?option=com_fabrik&amp;view=details&amp;fabrik=".$formModel->get('id')."&amp;rowid=".JRequest::getVar('rowid');
		$editlink = "<a href=\"$editURL\">" . JText::_('EDIT') . "</a>";
		$viewlink = "<a href=\"$viewURL\">" . JText::_('VIEW') . "</a>";
		$message = str_replace('{fabrik_editlink}', $editlink, $message);
		$message = str_replace('{fabrik_viewlink}', $viewlink, $message);
		$message = str_replace('{fabrik_editurl}', $editURL, $message);
		$message = str_replace('{fabrik_viewurl}', $viewURL, $message);

		$message = $w->parseMessageForPlaceHolder($message, $aData, false);

		$to = $w->parseMessageForPlaceHolder($params->get('receipt_to'), $aData, false);
		if (empty($to)) {
			// $$$ hugh - not much point trying to send if we don't have a To address
			// (happens frequently if folk don't properly validate their form inputs and are using placeholders)
			// @TODO - might want to add some feedback about email not being sent
			return;
		}

		/*
		// $$$ hugh - this code doesn't seem to be used?
		// it sets $email, which is then never referenced?
		$receipt_email = $params->get('receipt_to');
		if (!$form->record_in_database) {
			foreach ($aData as $key=>$val) {
				$aBits = explode('___', $key);
				$newKey = array_pop( $aBits);
				if ($newKey == $receipt_email) {
					$email = $val;
				}
			}
		}
		*/
		

		$subject =  html_entity_decode($params->get('receipt_subject', ''));
		$subject = $w->parseMessageForPlaceHolder($subject, null, false);
		$from 		= $config->getValue('mailfrom');
		$fromname = $config->getValue('fromname');
		//darn silly hack for poor joomfish settings where lang parameters are set to overide joomla global config but not mail translations entered
		$rawconfig = new JConfig();
		if ($from === '') {
			$from = $rawconfig->mailfrom;
		}
		if ($fromname === '') {
			$fromname= $rawconfig->fromname;
		}
		$res = JUTility::sendMail( $from, $fromname, $to, $subject, $message, true);
	}
}
?>