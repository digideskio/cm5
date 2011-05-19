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
 *  along with CM5. If not, see <http://www.gnu.org/licenses/>.
 *
 *  Contributors:
 *      Sque - initial API and implementation
 */

/**
 * Interface that must be followed by modules
 *
 * This will be typically used by modules.
 * @author sque
 *
 */
abstract class CM5_Module extends CM5_Configurable
{
	/**
	 * Store the merging result of meta_info
	 * @var array
	 */
	private $meta_info;

	/**
	 * Nickname of module
	 * @var unknown_type
	 */
	private $nickname;

	/**
	 * Storage of all user actions
	 * @var array
	 */
	private $user_actions = array();

	/**
	 * Instantiate the module
	 * @param string $nickname Its nickname
	 * @param array $meta_info Extra options.
	 */
	public function __construct($nickname, $meta_info)
	{
		$this->nickname = $nickname;
		$this->meta_info = array_merge(array(
			'title' => $nickname,
			'context' => null),
		$meta_info);
	}

	/**
	 * It will be executed when the module is initialized.
	 */
	abstract public function onInitialize();


	public function getConfigNickname()
	{
		return $this->nickname;
	}

	/**
	 * @return string The type of the module
	 */
	public function getModuleType()
	{
		return 'generic';
	}

	/**
	 * Check if this module is currently enabled.
	 */
	public function isEnabled()
	{
		return in_array($this->getConfigNickname(), explode(',', CM5_Config::getInstance()->enabled_modules));
	}

	/**
	 * Override to execute additional code when module is enabled.
	 * @return boolean If there was a problem you can return false to prevent
	 * module from enabling it.
	 */
	public function onEnable() {}

	/**
	 * Override to execute additional code when module is disabled
	 */
	public function onDisable() {}

	/**
	 * Ask module to be initialized
	 */
	public function initialize($working_context = 'frontend')
	{
		if (($this->meta_info['context'] != null) && ($this->meta_info['context'] != $working_context))
			return;	// Dont load this module as it asked for it.
		$this->onInitialize();
	}

	/**
	 * Get meta info
	 */
	public function getMetaInfo()
	{
		return $this->meta_info;
	}

	/**
	 * Helper function to get an entry from getMetaInfo()
	 * @param string $name The name of the info property
	 */
	public function getMetaInfoEntry($name)    {

		if (!isset($this->meta_info[$name]))
		return null;
		return $this->meta_info[$name];
	}

	/**
	 * Declare a new user action of this module
	 * @param string $name A unique name for the action, slug
	 * @param string $display The title of this action
	 * @param callable $method A callable object to be executed to
	 * process this action
	 */
	public function declareAction($name, $display, $method)
	{
		$class_name = get_class($this);
		if (!method_exists($this, $method))
		throw new InvalidArgumentException("Class $class_name has no method with name $method");
		$this->user_actions[$name] = array(
            'name' => $name,
            'display' => $display,
            'callback' => array($this, $method)
		);
	}

	/**
	 * Get all actions of this module
	 * @return array All the registered actions in one array.
	 */
	public function getActions()
	{
		return $this->user_actions;
	}

	/**
	 * Get one specific action
	 * @param string $name
	 * @return array
	 */
	public function getAction($name)
	{
		if (!isset($this->user_actions[$name]))
			return null;
		return $this->user_actions[$name];
	}
}
