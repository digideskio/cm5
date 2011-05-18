<?php
/*
 *  This file is part of PHPLibs <http://phplibs.kmfa.net/>.
 *
 *  Copyright (c) 2010 < squarious at gmail dot com > .
 *
 *  PHPLibs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  PHPLibs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with PHPLibs.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Layout is a base class to create and manage
 * all layouts of your system.
 */
class Layout
{
	/**
	 * @var Output_HTMLDoc
	 */
	private $document;

	/**
	 * Array with all slots of this layout
	 * @var array
	 */
	private $slots = array();

	/**
	 * @var EventDispatcher
	 */
	private $events = null;
	
	/**
	 * Pointer to active layout-slot
	 * @var array
	 */
	static private $active = null;

	/**
	* To instantiate this class use getInstance().
	*/
	final public function __construct()
	{
		$class = get_called_class();
		// Check if there is already a layout with that name
		if (isset(self::$instances[$class]) && is_object(self::$instances[$class]))
			throw new RuntimeException("There is already a layout with name {$class}");

		// Register myself
		self::$instances[$class] = $this;

		$this->document = new Output_HTMLDoc();
		
		$this->setSlot('default', $this->document->get_body());

		// Call initialize method
		$this->onInitialize();
	}

	/**
	 * Get event dispather.
	 * @return EventDispatcher Available events are:
	 *  - pre-flush : Called just before flushing this layout
	 *  - post-flush : Called after flushing this layout.
	 */
	public function events()
	{
		if ($this->events !== null)
			return $this->events;
			
		$this->events = new EventDispatcher(array(
			'pre-flush',
			'post-flush'
		));
		return $this->events;
	}

	/**
	 * Get the document of this layout
	 * @return Output_HTMLDoc 
	 */
	public function getDocument()
	{
		return $this->document;
	}

	/**
	 * Activate and capture all output on this slot.
	 */ 
	public function activateSlot($slot_name = 'default')
	{
		if (self::$active !== null)
			self::$active['layout']->deactivate();

		// Set output buffer
		self::$active = array(
			'layout' => $this,
			'slot' => $slot_name
		);
		ob_start(array(self::$active['layout']->slots[$slot_name], 'append_text'));
		self::$active['layout']->slots[$slot_name]->push_parent();

		return $this;
	}

	/**
	 * Check if this layout is the active one
	 */
	public function isActive()
	{
		return (self::$active)
			?self::$active['layout'] === $this
			:false;
	}
	
	/**
	 * Check if a slot of this layout is active
	 * @param $slot_name The name of the slot
	 */
	public function isSlotActive($slot_name)
	{
		if (!$this->isActive())
			return false;
		return self::$active['slot'] == $slot_name;
	}
	
	/**
	 * Get the currently active layout
	 * @return Layout
	 */
	static public function getActive()
	{
		return (self::$active)?self::$active['layout']:null;
	}
	
	
	/**
	 *  Deactivate layout and flush layout on output
	 */
	public function flush()
	{
		if ($this->isActive())
		{
			if ($this->events)
				$this->events()->notify('pre-flush', array('layout' => $this));

			// Unregister output gatherers
			Output_HTMLTag::pop_parent();
			ob_end_clean();

			if ($this->events)
				$this->events()->notify('post-flush', array('layout' => $this));

			echo $this->document->render();

			self::$active = null;
		}
	}

	/**
	 * Deactivate if any active slot on this layout.
	 */
	public function deactivate()
	{
		if ($this->isActive())
		{
			// Unregister output gatherers
			Output_HTMLTag::pop_parent();
			ob_end_clean();

			self::$active = null;
		}
	}

	/**
	 * Assign an element on a slot
	 * @param string $slot_name The nickname of slot.
	 * @param Output_HTMLTag $element the element to be assigned.
	 */
	public function setSlot($slot_name, $element)
	{
		if ($this->isSlotActive($slot_name))
		{
			$this->deactivate();
			$this->slots[$slot_name] = $element;
			$this->activateSlot($slot_name);
		}
		else
			$this->slots[$slot_name] = $element;
	}

	/**
	 * Hold all the instances of layouts
	 * @var array
	 */
	static private $instances = array();

	/**
	 * Get the instance of a layout based on its "static" name.
	 * @return Layout
	 */
	static public function getInstance()
	{
		$class = get_called_class();
		if (isset(self::$instances[$class]))
			return self::$instances[$class];
		
		return self::$instances[$class] = new $class();
	}
}
