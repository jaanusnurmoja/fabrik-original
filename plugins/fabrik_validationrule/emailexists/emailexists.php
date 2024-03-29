<?php
/**
*
* @package fabrikar
* @author Hugh Messenger
* @copyright (C) Hugh Messenger
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/validation_rule.php');

class plgFabrik_ValidationruleEmailExists extends plgFabrik_Validationrule
{

	var $_pluginName = 'emailexists';

	/** @param string classname used for formatting error messages generated by plugin */
	var $_className = 'emailexists';

	/** @var bool if true uses icon of same name as validation, otherwise uses png icon specified by $icon */
	protected $icon = 'isemail';

	/**
	 * validate the elements data against the rule
	 * @param	string	data to check
	 * @param	object	element
	 * @param	int		plugin sequence ref
	 * @return	bool	true if validation passes, false if fails
	 */

	function validate($data, &$elementModel, $c)
	{
		if (empty($data))
		{
			return false;
		}
		$params = $this->getParams();
		//as ornot is a radio button it gets json encoded/decoded as an object
		$ornot = (object)$params->get('emailexists_or_not');
		$ornot = isset($ornot->$c) ? $ornot->$c : 'fail_if_exists';

		$user_field = (array)$params->get('emailexists_user_field', array());
		$user_field = $user_field[$c];
		$user_id = 0;
		if ((int)$user_field !== 0)
		{
			$user_elementModel = FabrikWorker::getPluginManager()->getElementPlugin($user_field);
			$user_fullName = $user_elementModel->getFullName(false, true, false);
			$user_field = $user_elementModel->getFullName(false, false, false);
		}

		if (!empty($user_field))
		{
			// $$$ the array thing needs fixing, for now just grab 0
			$formdata = $elementModel->getForm()->_formData;
			$user_id = JArrayHelper::getValue($formdata, $user_fullName . '_raw', JArrayHelper::getValue($formdata, $user_fullName, ''));
			if (is_array($user_id))
			{
				$user_id = JArrayHelper::getValue($user_id, 0, '');
			}
		}

		jimport('joomla.user.helper');
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id')->from('#__users')->where('email = ' . $db->quote($data));
		$db->setQuery($query);
		$result = $db->loadResult();
		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			if (!$result)
			{
				if ($ornot == 'fail_if_exists')
				{
					return true;
				}
			}
			else
			{
				if ($ornot == 'fail_if_not_exists')
				{
					return true;
				}
			}
			return false;
		}
		else
		{
			if (!$result)
			{
				return ($ornot == 'fail_if_exists') ? true : false;
			} else {
				if ($user_id != 0) {
					if ($result == $user_id) {
						return ($ornot == 'fail_if_exists') ? true : false;
					}
					return false;
				}
				else {
					if ($result == $user->get('id')) // The connected user is editing his own data
					{
						return ($ornot == 'fail_if_exists') ? true : false;
					}
					return false;
				}
			}
		}
		return false;
	}

	/**
	* gets the hover/alt text that appears over the validation rule icon in the form
	* @param	object	element model
	* @param	int		repeat group counter
	* @return	string	label
	*/

	protected function getLabel($elementModel, $c)
	{
		$params = $this->getParams();
		//as ornot is a radio button it gets json encoded/decoded as an object
		$ornot = (object) $params->get('emailexists_or_not');
		$c = (int) $c;
		$cond = '';
		foreach ($ornot as $k => $v)
		{
			if ($k == $c)
			{
				$cond = $v;
			}
		}
		if ($cond == 'fail_if_not_exists')
		{
			return JText::_('PLG_VALIDATIONRULE_EMAILEXISTS_LABEL_NOT');
		}
		else
		{
			return parent::getLabel($elementModel, $c);
		}
	}

}
?>