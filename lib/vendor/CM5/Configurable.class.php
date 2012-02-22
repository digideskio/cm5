<?php
/*
 *  This file is part of CM5 <http://code.0x0lab.org/p/cm5>.
*
*  Copyright (c) 2010 Sque.
*
*  CM5 is free software: you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published
*  the Free Software Foundation, either version 3 of the License, or
*  (at your option) any later version.
*
*  CM5 is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with CM5.  If not, see <http://www.gnu.org/licenses/>.
*
*  Contributors:
*      Sque - initial API and implementation
*/

/**
 * Interface that must be followed by configurable objects
 *
 * This will be typically used by modules and themes.
 * @author sque
 *
 */
abstract class CM5_Configurable
{
	/**
	 * Implement with objects configuration nickname
	 * @return string The unique nickname of this object.
	 */
	abstract public function getConfigNickname();

	/**
	 * Get an array with configurable fields.
	 * @return array Associative array with the following subfields.
	 *  - @b label A label for this field.
	 *  - @b type The type of the option (text, select, checkbox, color)
	 *  - @b options If the type is option based
	 *  .
	 */
	public function getConfigurableFields()
	{
		return array();
	}

	/**
	 * Default configuration of the module
	 * @return array Associative array with default configuration of the module.
	 */
	public function getDefaultConfiguration()
	{
		return array();
	}

	/**
	 * Internal pointer to actual config object
	 * @var Zend_Config
	 */
	private $config = null;

	/**
	 * Load configuration for this module from CM5_Config and return it
	 * @return Zend_Config Configuration object of this object
	 */
	public function getConfig()
	{
		// Return instance object
		if ($this->config !== null)
		return $this->config;

		// Load configuration from global config
		$CM5_Config = CM5_Config::getInstance();
		$nickname = $this->getConfigNickname();
		if (isset($CM5_Config->module->$nickname)) {
			$this->config = new Zend_Config(
			array_merge($this->getDefaultConfiguration(), $CM5_Config->module->$nickname->toArray()), true);
		} else {
			$this->config = new Zend_Config($this->getDefaultConfiguration(), true);
		}

		return $this->config;
	}

	/**
	 * Save the actual configuration at CM5_Config
	 */
	public function saveConfig()
	{
		$CM5_Config = CM5_Config::getWritableCopy();
		$CM5_Config->module->{$this->getConfigNickname()} = $this->config;
		CM5_Config::update($CM5_Config);

		$this->onSaveConfig();
	}

	/**
	 * Override this function if you want extra work on after saving configuration
	 */
	public function onSaveConfig(){
	}
}
