<?php

/**
 * A cron task to import gmail emails into a specified table
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-cron.php');

class plgFabrik_Crongmail extends plgFabrik_Cron {

	/**
	 * do the plugin action
	 * @param array data
	 * @param object list model
	 * @return number of records updated
	 */

	public function process(&$data, &$listModel)
	{
		$params = $this->getParams();

		$email = $params->get('plugin-options.email');
		$pw = $params->get('plugin-options.password');
		if ($email == '' || $pw == '') {
			return;
		}

		$server = $params->get('plugin-options.server', '{imap.gmail.com:993/imap/ssl}');
		$inboxes = explode(',', $params->get('plugin-options.inboxes', 'INBOX'));

		$deleteMail = false;
		//$storageType = 'filesystemstorage';
		//require_once(JPATH_BASE.DS.'components'.DS.'com_fabrik'.DS.'plugins'.DS.'element'.DS.'fabrikfileupload'.DS.'adaptors'.DS.$storageType.'.php');
		$p = new stdClass();

		$fromField = $params->get('plugin-options.from');
		$titleField = $params->get('plugin-options.title');
		$dateField = $params->get('plugin-options.date');
		$contentField = $params->get('plugin-options.content');

		//	$storage = new $storageType( $p);
		//$imageLib = FabimageHelper::loadLib('GD2');
		//	$imageLib->setStorage($storage);

		$storeData 	= array();
		$numProcessed = 0;
		foreach ($inboxes as $inbox) {
			$url = $server.$inbox;
			$mbox = imap_open ($url, $email, $pw);

			if (!$mbox) {
				JError::raiseNotice(400, JText::_("PLG_CRON_GMAIL_ERROR_CONNECT") . imap_last_error());
				continue;
			}

			$MC = imap_check( $mbox);

			$mailboxes = imap_list($mbox, $server, '*');
			echo "<pre>";print_r($mailboxes);
			$lastid = $params->get('plugin-options.lastid', 0);

			if ($lastid == 0) {
				$result = imap_fetch_overview($mbox, "1:$MC->Nmsgs");
				echo $lastid;
				$mode = 0; //retrieve emails by message number
			} else {
				// retrieve emails by message id;
				$result = imap_fetch_overview($mbox, "$lastid:*", FT_UID);
				if(count($result)>0) {
					unset($result[0]);
				}
			}
			// Fetch an overview for all messages in INBOX
			//$result = imap_fetch_overview($mbox, "1:$lastid", $mode);

print_r($result);exit;
			$numProcessed += count($result);
			foreach ($result as $overview) {
				if ($overview->uid > $lastid) {
					$lastid = $overview->uid;
				}

				$content = '';
				$thisData = array();

				preg_match( "/<(.*)>/", $overview->from, $matches);

				$thisData[$fromField] = $overview->from;
				$thisData[$titleField] = $this->getTitle( $overview);
				$thisData[$dateField] = JFactory::getDate($overview->date)->toMySQL();
				$thisData['imageFound'] = false;

				$thisData[$fromField] = (empty( $matches )) ? $overview->from : "<a href=\"mailto:$matches[1]\">$overview->from</a>";
				//use server time for all incomming messages.
				$date = JFactory::getDate();
				$thisData['processed_date'] = $date->toMySQL();
				$struct = imap_fetchstructure( $mbox, $overview->msgno);
				$parts = create_part_array($struct);
				foreach($parts as $part) {

					//type 5 is image - full list here http://algorytmy.pl/doc/php/function.imap-fetchstructure.php
					if($part['part_object']->type == 5) {
						$filecontent = imap_fetchbody( $mbox, $overview->msgno, $part['part_number']);

						$attachmentName = '';
						$pname = 'parameters';
						if (is_object($part['part_object']->parameters) ) {
							//can be in dparamenters instead?
							$pname= 'dparameters';
						}
						$attarray = $part['part_object']->$pname;
						if ($attarray[0]->value == "us-ascii" || $attarray[0]->value == "US-ASCII") {
							if ($attarray[1]->value != "") {
								$attachmentName = $attarray[1]->value;
							}
						} elseif ($attarray[0]->value != "iso-8859-1" && $attarray[0]->value != "ISO-8859-1" && $attarray[0]->value != 'utf-8') {
							$attachmentName = $attarray[0]->value;
						}

						if ($attachmentName != '') {
							//randomize file name
							$ext = JFile::getExt($attachmentName);
							$name = JFile::stripExt($attachmentName);
							$name .= '-' . JUserHelper::genRandomPassword(5).'.'.$ext;
							$thisData['attachmentName'] = $name;
							$thisData['imageFound'] = true;
							$fileContent = imap_fetchbody( $mbox, $overview->msgno, 2);
							$thisData['imageBuffer'] = imap_base64( $filecontent);
						}
					}
					/*
					 * Message parts - third param in imap_fetchbody
					 * (empty) - Entire message
						0 - Message header
						1 - MULTIPART/ALTERNATIVE
						1.1 - TEXT/PLAIN
						1.2 - TEXT/HTML
						2 - file.ext
					 */

					$content =  @imap_fetchbody( $mbox, $overview->msgno, 1.2); //html

					if (strip_tags($content) == '') {
						$content =  @imap_fetchbody( $mbox, $overview->msgno, 1.1); //plain text
					}

					//this encodes text with  =20 correctly i think
					//may need to test that $part['encoding'] = 4	(QUOTED-PRINTABLE)
					$content = imap_qprint($content);

					// hmm this seemed to include encoded text which imap_base64 couldnt sort out
					//as the encoding was too long for insert query - shouts were not getting through
					// think it might be to do with $part being type 5 (image)
					//
					// now only adding if part type is 0

					if (strip_tags($content) == '') {

						if($part['part_object']->type == 0) {
							$content =  @imap_fetchbody( $mbox, $overview->msgno, 1); //multipart alternative
						}
					}
				}
				$content = $this->removeReplyText($content);
				//remove any style sheets
				$content =  preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si', ' ', $content);
				$thisData[$contentField] = $content;

				foreach($thisData as $key=>$val) {
					JRequest::setVar($key, $val);
				}
				$formModel = $listModel->getForm();
				unset($listModel->getFormModel()->_formData);
				$listModel->getFormModel()->process();

				//// TEST!!!!!!!

				if ($deleteMail) {
					imap_delete( $mbox, $overview->msgno);
				}

			}
		}
		$params->set('plugin-options.lastid', $lastid);
		$this->_row->params = $params->toString();
		$this->_row->store();

		/*	foreach ($storeData as &$data) {
			$relLargeImagePath = '';
			$largeImagePath = '';
			$smallImagePath = '';
			if ($data['imageFound']) {
			// @TODO process images to fileupload element
			if (isset($data['imageBuffer'] )) {
			$relLargeImagePath = DS.'media'.DS.'com_fabrik'.DS.$data['id'].DS.'galleries'.DS.'images'.DS.$data['attachmentName'];
			$largeImagePath = JPATH_BASE.$relLargeImagePath;

			$smallImagePath = JPATH_BASE.DS.'media'.DS.'com_fabrik'.DS.$data['id'].DS.'galleries'.DS.'thumbs'.DS.$data['attachmentName'];
			JFile::write( $largeImagePath, $data['imageBuffer']);
			$imageLib->resize(400, 400, $largeImagePath, $largeImagePath);
			$imageLib->resize(125, 125, $largeImagePath, $smallImagePath);
			$title = "<a href='".JURI::base()."/media/com_fabrik/".$data['id']."/galleries/images/".$data['attachmentName']."' rel='lightbox[]' title='".$data['attachmentName']."'>
			<img class='fabrikLightBoxImage' src='".JURI::base()."/media/com_fabrik/".$data['id']."/galleries/thumbs/".$data['attachmentName']."' alt='media' /></a>" . $title;
			}
			}
			}*/

		imap_expunge($mbox);
		imap_close($mbox);
		return $numProcessed;
	}

	/**
	 * try to remove reply text from emails
	 * @param string content
	 * @return string content
	 */

	protected function removeReplyText($content ) {
		// try to remove reply text
		$content = preg_replace( "/\n\>(.*)/", '', $content);
		$content = explode("\n", $content);
		for($i = count($content)-1; $i >= 0; $i--) {
			if (trim($content[$i]) == '') {
				unset($content[$i]);
			}
		}
		$last = array_pop($content);
		$content = implode("\n", $content);

		// test for date and message that preceeds reply text
		//e.g. "2009/9/2 Dev Site for Play Simon Games "
		$matches= array();
		$res = preg_match( "/[0-9]{4}\/[0-9]{1,2}\/[0-9]{1,2}/", $last, $matches);
		if ($res == 0) {
			$content .= "\n$last";
		}
		return $content;
	}

	/**
	 * get subject of email
	 * @param $overview
	 * @return string email subject
	 */

	private function getTitle( $overview )
	{
		$title = $overview->subject;
		//remove 'RE: ' from title
		if (strtoupper(substr($title, 0, 3)) == 'RE:') {
			$title = substr($title, 3, strlen($title));
		}
		return $title;
	}

}

function create_part_array($structure, $prefix="") {
	if (isset($structure->parts)&& sizeof($structure->parts) > 0) {    // There some sub parts
		foreach ($structure->parts as $count => $part) {
			add_part_to_array($part, $prefix.($count+1), $part_array);
		}
	}else{    // Email does not have a seperate mime attachment for text
		$part_array[] = array('part_number' => $prefix.'1', 'part_object' => $structure);
	}
	return $part_array;
}
// Sub function for create_part_array(). Only called by create_part_array() and itself.
function add_part_to_array($obj, $partno, & $part_array) {
	$part_array[] = array('part_number' => $partno, 'part_object' => $obj);
	if ($obj->type == 2) { // Check to see if the part is an attached email message, as in the RFC-822 type
		if (sizeof($obj->parts) > 0) {    // Check to see if the email has parts
			foreach ($obj->parts as $count => $part) {
				// Iterate here again to compensate for the broken way that imap_fetchbody() handles attachments
				if (sizeof($part->parts) > 0) {
					foreach ($part->parts as $count2 => $part2) {
						add_part_to_array($part2, $partno.".".($count2+1), $part_array);
					}
				}else{    // Attached email does not have a seperate mime attachment for text
					$part_array[] = array('part_number' => $partno.'.'.($count+1), 'part_object' => $obj);
				}
			}
		}else{    // Not sure if this is possible
			$part_array[] = array('part_number' => $prefix.'.1', 'part_object' => $obj);
		}
	}else{    // If there are more sub-parts, expand them out.
		if(isset($obj->parts) && is_array($obj->parts)) {
			if (sizeof($obj->parts) > 0) {
				foreach ($obj->parts as $count => $p) {
					add_part_to_array($p, $partno.".".($count+1), $part_array);
				}
			}
		}
	}
}
?>