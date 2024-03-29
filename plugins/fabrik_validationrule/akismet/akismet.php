<?php
/**
 *
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!

defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/validation_rule.php');

class plgFabrik_ValidationruleAkismet extends plgFabrik_Validationrule
{
	var $_pluginName = 'akismet';

	/** @param string classname used for formatting error messages generated by plugin */
	var $_className = 'akismet';
	
	/** @var bool if true uses icon of same name as validation, otherwise uses png icon specified by $icon */
	protected $icon = 'notempty';

	/**
	 * validate the elements data against the rule
	 * @param	string	data to check
	 * @param	object	element model
	 * @param	int		plugin sequence ref
	 * @return	bool	true if validation passes, false if fails
	 */

	function validate($data, &$elementModel, $c)
	{
		$params = $this->getParams();
		$user = JFactory::getUser();
		if ($params->get('akismet-key') != '')
		{
			$username = $user->get('username') != '' ? $user->get('username') : $this->_randomSring();
			$email = $user->get('email') != '' ? $user->get('email') : $this->_randomSring()  .'@' . $this->_randomSring() . 'com';
			require_once(JPATH_COMPONENT . '/plugins/validationrule/akismet/akismet.class.php');
			$akismet_comment = array (
				'author' => $username,
				'email' => $user->get('email'),
				'website' => JURI::base(),
				'body' => $data
			);
			$akismet = new Akismet(JURI::base(), $params->get('akismet-key'), $akismet_comment);
			if ($akismet->errorsExist())
			{
				JError::raiseNotice( JText::_("Couldn't connected to Akismet server!"));
			}
			else
			{
				if ($akismet->isSpam())
				{
					return false;
				}
			}
		}
		return true;
	}

	function _randomSring()
	{
		return preg_replace('/([ ])/e', 'chr(rand(97,122))', '     ');
	}
}
?>