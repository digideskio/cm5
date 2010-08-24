<?php

//! Interface to implement modules
abstract class CMS_Module extends CMS_Configurable
{
    public function config_nickname()
    {
        return $this->info_property('nickname');
    }
    
    //! Array with module info
    abstract public function info();
    
    //! Initialize this module
    abstract public function init();
    
    //! Get a specific module info
    public function info_property($name)
    {
        $minfo = $this->info();
        if (!isset($minfo[$name]))
            return null;
        return $minfo[$name];
    }
        
    //! Repository of all user actions
    private $user_actions = array();
    
    //! Declare a new user action of this module
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
    
    //! Get all actions of this module
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
    
    //! Register this module to core
    public static function register()
    {
        $module_class = get_called_class();
        CMS_Core::get_instance()->register_module( new $module_class() );
    }

}
