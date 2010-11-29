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
    public function config_nickname()
    {
        return $this->info_property('nickname');
    }

    /**
     * @return string The type of the module
     */
    public function module_type()
    {
        return 'generic';
    }
    
	/**
	 * Check if this module is currently enabled.
	 */
    public function is_enabled()
    {
        return in_array($this->config_nickname(), explode(',', GConfig::get_instance()->enabled_modules));
    }

    /**
     * Must be implemented by modules to provide their information.
     * @return array Associative array with module info.
     */ 
    abstract public function info();
    
    /**
     * It will be executed when the module is initialized.
     */
    abstract public function init();
    
    /**
     * Helper function to get an entry from info()
     * @param string $name The name of the info property
     */
    public function info_property($name)
    {
        $minfo = $this->info();
        if (!isset($minfo[$name]))
            return null;
        return $minfo[$name];
    }
        
    /**
     * Storage of all user actions
     * @var array
     */
    private $user_actions = array();
    
    /**
     * Declare a new user action of this module
     * @param string $name A unique name for the action, slug
     * @param string $display The title of this action
     * @param callable $method A callable object to be executed to
     * process this action
     */
    public function declare_action($name, $display, $method)
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
    public function get_actions()
    {
        return $this->user_actions;
    }
    
    //! Get an action
    public function get_action($name)
    {
        if (!isset($this->user_actions[$name]))
            return null;
        return $this->user_actions[$name];
    }
    
    /**
     * Helper function to register this module to core
     * @see CM5_Core::register_module()
     */
    public static function register()
    {
        $module_class = get_called_class();
        CM5_Core::get_instance()->register_module( new $module_class() );
    }

}
