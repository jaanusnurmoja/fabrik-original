<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

abstract class FabrikWebService
{
	
	/**
	* @var    array  FabrikWebService instances container.
	* @since  3.0.5
	*/
	protected static $instances = array();

	
	public static function getInstance($options = array())
	{
		// Sanitize the database connector options.
		$options['driver'] = (isset($options['driver'])) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']) : 'soap';
		$options['endpoint'] = (isset($options['endpoint'])) ? $options['endpoint'] : null;
		
		// Get the options signature for the database connector.
		$signature = md5(serialize($options));
		
		// If we already have a database connector instance for these options then just use that.
		if (empty(self::$instances[$signature]))
		{
		
			// Derive the class name from the driver.
			$class = 'FabrikWebService' . ucfirst($options['driver']);
		
			// If the class doesn't exist, let's look for it and register it.
			if (!class_exists($class))
			{
		
				// Derive the file path for the driver class.
				$path = dirname(__FILE__) . '/webservice/' . $options['driver'] . '.php';
		echo "path = $path <br>";
				// If the file exists register the class with our class loader.
				if (file_exists($path))
				{
					echo "$class, $path <br>";
					JLoader::register($class, $path);
				}
				// If it doesn't exist we are at an impasse so throw an exception.
				else
				{
		
					// Legacy error handling switch based on the JError::$legacy switch.
					// @deprecated  12.1
		
					if (JError::$legacy)
					{
						// Deprecation warning.
						JLog::add('JError is deprecated.', JLog::WARNING, 'deprecated');
						JError::setErrorHandling(E_ERROR, 'die');
						return JError::raiseError(500, JText::sprintf('JLIB_DATABASE_ERROR_LOAD_DATABASE_DRIVER', $options['driver']));
					}
					else
					{
						throw new Exception(JText::sprintf('JLIB_DATABASE_ERROR_LOAD_DATABASE_DRIVER', $options['driver']));
					}
				}
			}
			// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
			if (!class_exists($class))
			{
		
				// Legacy error handling switch based on the JError::$legacy switch.
				// @deprecated  12.1
		
				if (JError::$legacy)
				{
					// Deprecation warning.
					JLog::add('JError() is deprecated.', JLog::WARNING, 'deprecated');
		
					JError::setErrorHandling(E_ERROR, 'die');
					return JError::raiseError(500, JText::sprintf('JLIB_DATABASE_ERROR_LOAD_DATABASE_DRIVER', $options['driver']));
				}
				else
				{
					throw new Exception(JText::sprintf('JLIB_DATABASE_ERROR_LOAD_DATABASE_DRIVER', $options['driver']));
				}
			}
		
			// Create our new FabrikWebService connector based on the options given.
			try
			{
				$instance = new $class($options);
			}
			catch (Exception $e)
			{
		
				// Legacy error handling switch based on the JError::$legacy switch.
				// @deprecated  12.1
		
				if (JError::$legacy)
				{
					// Deprecation warning.
					JLog::add('JError() is deprecated.', JLog::WARNING, 'deprecated');
		
					JError::setErrorHandling(E_ERROR, 'ignore');
					return JError::raiseError(500, JText::sprintf('JLIB_DATABASE_ERROR_CONNECT_DATABASE', $e->getMessage()));
				}
				else
				{
					throw new Exception(JText::sprintf('JLIB_DATABASE_ERROR_CONNECT_DATABASE', $e->getMessage()));
				}
			}
		
			// Set the new connector to the global instances based on signature.
			self::$instances[$signature] = $instance;
		}
		
		return self::$instances[$signature];
	}
	
	public function setMap($map)
	{
		// how to map the data from the web service to a Fabrik list
		/* $this->map = array(
		 array('from' => '{EventId}', 'to' => $fk),
		array('from' => '{Genre}', 'to' => 'genres'),
		array('from' => '{DoorOpen}', 'to' => 'Event_date'),
		array('from' => '{DoorClosed}', 'to' => 'Event_date_end'),
		array('from' => '{DoorClosed}', 'to' => 'Event_publish_end'),
		array('from' => '{IsFreeEntrance}', 'to' => 'ticket_cost', 'value' => 0, 'match' => 'true'),
		array('from' => '{AvailabilityStatus}', 'to' => 'ticket_cost', 'value' => 3, 'match' => 'false'),
		array('from' => '{ShortText}', 'to' => 'Event_description_short'),
		array('from' => '{Title} ({SubTitle})', 'to' => 'Event_title'),
		array('from' => '{Price}', 'to' => 'Event_price_presale', 'match' => 'foreach($d->Price as $p) { if($p->TicketType == \'Normaal\') { return $p->Price;}} return false;', 'eval' => true),
		array('from' => '{Price}', 'to' => 'Event_price_door', 'match' => 'foreach($d->Price as $p) { if($p->TicketType == \'Dagkassa\') { return $p->Price;}} return false;', 'eval' => true)
		); */
		$this->map = $map;
	}
	
	public function map($datas, $fk)
	{
		$return = array();
		$w = new FabrikWorker();
		foreach ($datas as $data)
		{
			$row = array();
			foreach ($this->map as $map)
			{
				$to = $map['to'];
				$map['from'] = $w->parseMessageForPlaceHolder($map['from'], $data, false);
				
				if (JArrayHelper::getValue($map, 'match', '') !== '')
				{
					if (JArrayHelper::getValue($map, 'eval') == 1)
					{
						$res = eval($map['match']);
						if ($res !== false)
						{
							$row[$to] = $res;
						}
					}
					else 
					{
						if ($map['match'] == $map['from'])
						{
							$row[$to] = $map['value'];
						}
					}
				}
				else
				{
					$row[$to] = $map['from'];
				}
			}
			$return[] = $row;
		}
		return $return;
	}
	
	/**
	* query the web service to get the data
	* @param	string	method to call at web service (soap only)
	* @param	array	key value filters to send to web service to filter the data
	* @param	string	$startPoint of actual data, if soap this is an xpath expression, otherwise its a key.key2.key3 string to traverse the 
	* returned data to arrive at the data to map to the fabrik list
	* @param	string Result method name - soap only, if not set then "$method . 'Result' will be used.
	* @return	array	series of objects which can then be bound to the list using storeLocally() 
	*/
	
	abstract function get($method, $options = array(), $startPoint = null, $result = null);
	
	/**
	 * store the data obtained from get() in a list
	 * @param	object	$listModel to store the data in
	 * @param	array	$data obtained from get()
	 * @param	string	foreign key to map records in $data to the list models data.
	 * @param	bool	should existing matched rows be updated or not?
	 */
	
	public function storeLocally($listModel, $data, $fk, $update)
	{
		$data = $this->map($data, $fk);
		$item = $listModel->getTable();
		$formModel = $listModel->getFormModel();
		$db = $listModel->getDb();
		$item = $listModel->getTable();
		$query = $db->getQuery(true);
		$query->select($item->db_primary_key . ' AS id, ' . $fk)->from($item->db_table_name);
		$db->setQuery($query);
		$ids = $db->loadObjectList($fk);
		$formModel->getGroupsHiarachy();
		$this->updateCount = 0;
		$this->addedCount = 0;
		foreach ($data as $row)
		{
			foreach ($row as $k => $v)
			{
				$elementModel = $formModel->getElement($k, true);
				$row[$k] = $elementModel->fromXMLFormat($v);
			}
			$pk = '';
			if (array_key_exists($row[$fk], $ids) && $row[$fk] != '')
			{
				$pk = $ids[$row[$fk]]->id;
			}
			if (!$update && $pk !== '')
			{
				continue;
			}
			if ($pk == '')
			{
				$this->addedCount ++;
			}
			else
			{
				$this->updateCount ++;
			}
			$listModel->storeRow($row, $pk);
		}
	}
	
	/**
	 * parse the filter values into driver type
	 * @param	string	$val
	 * @param	string	$type
	 */
	
	public function getFilterValue($val, $type)
	{
		switch ($type)
		{
			case 'bool':
				$val = (bool)$val;
				break;
			case 'date':
				$d = JFactory::getDate($val);
				$val = $d->toISO8601();
				break;
			case 'text':
				break;
		}
		return $val;
	}
}
?>