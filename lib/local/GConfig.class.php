<?php

//! Global configuration helper
class GConfig
{
    //! The file to be used to save/load configuration
    public static $config_file = null;
    
    //! Default configuraiton
    public static $default_config = array();
    
    //! Load configuration from file
    public static function load_config()
    {
        $config = new Zend_Config(self::$default_config, true);
        $config->merge(new Zend_Config(require self::$config_file));
        $config->setReadOnly();
        Registry::set('config2', $config);
    }
    
    //! Get the instance of global config
    public static function get_instance()
    {
        return Registry::get('config2');
    }
    
    //! Get a writable copy of the configuration
    public static function get_writable_copy()
    {
        return new Zend_Config(self::get_instance()->toArray(), true);
    }
    
    //! Update configuration
    public static function update(Zend_Config $config)
    {
        if (!is_writable(self::$config_file))
            return;
        
        // Write file
        $conf_writer = new Zend_Config_Writer_Array(
            array(
                'config' => $config,
                'filename' => self::$config_file
            )
        );
        $conf_writer->write();
        
        // Update configuration
        self::load_config();
    }
}
