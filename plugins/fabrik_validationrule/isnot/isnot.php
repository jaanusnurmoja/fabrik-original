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

class plgFabrik_ValidationruleIsNot extends plgFabrik_Validationrule
{

	var $_pluginName = 'isnot';

	/** @param string classname used for formatting error messages generated by plugin */
	var $_className = 'notempty isnot';

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
		if (is_array($data))
		{
			$data = implode('', $data);
		}
		$params = $this->getParams();
		$isnot = $params->get('isnot-isnot');
		$isnot = $isnot[$c];
		$isnot = explode('|', $isnot);
		foreach ($isnot as $i)
		{
			if((string) $data === (string) $i)
			{
				return false;
			}
		}
		return true;
	}
}
?>