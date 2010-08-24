<?php

//! Interface to implement configurable modules
abstract class CMS_Configurable
{
    //! Implement with objects configuration nickname
    abstract public function config_nickname();
        
    //! Get an array with configuration options
    /**
     * Each entry is an associative array with the following fields.
     *  - @b name The name of this option
     *  - @b display The display to be shown 
     *  - @b type The type of the option (text, select, checkbox)
     *  - @b options If the type is option based
     *  .
     */
    public function config_options()
    {
        return array();
    }
    
    //! Default configuration of the module
    /**
     * An associative array with default configuration of the module.
     */
    public function default_config()
    {
        return array();
    }
    
    private $config = null;
    
    //! Get module configuration
    public function get_config()
    {
        // Return instance object
        if ($this->config !== null)
            return $this->config;

        // Read configuration from global config
        $gconfig = Registry::get('config');
        $nickname = $this->config_nickname();
        if (isset($gconfig->module->$nickname))
            $this->config = new Zend_Config($gconfig->module->$nickname->toArray(), true);
        else
            $this->config = new Zend_Config($this->default_config(), true);

        return $this->config;
    }
    
    //! Save module configuration
    public function save_config()
    {
        $gconfig = GConfig::get_writable_copy();
        $gconfig->module->{$this->config_nickname()} = $this->config;
        GConfig::update($gconfig);
        
        if (method_exists($this, 'on_save_config'))
            $this->on_save_config();
    }
    
}
