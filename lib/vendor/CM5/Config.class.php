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
 * CM5 Global configuration helper
 */
class CM5_Config
{
    //! The file to be used to save/load configuration
    public static $config_file = null;
    
    //! Default configuraiton
    public static $default_config = array();
    
    /**
     * Load configuration from file
     */
    public static function load()
    {
        $config = new Zend_Config(self::$default_config, true);
        $config->merge(new Zend_Config(require self::$config_file));
        $config->setReadOnly();
        Registry::set('config', $config);
    }
    
    /**
     * Get the singleton instance of CM5_Config
     * @return Zend_Config
     */
    public static function getInstance()
    {
        return Registry::get('config');
    }
    
    /**
     * Get a writtable copy of configuration file.
     * @return Zend_Config
     */
    public static function getWritableCopy()
    {
        return new Zend_Config(self::getInstance()->toArray(), true);
    }
    
    /**
     * Update configuration file based on a new config pack
     * @param Zend_Config $config
     */
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
        self::load();
    }
}
