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

//! Interface to implement themes
abstract class CM5_Theme extends CM5_Module
{
    //! Get theme layout class
    abstract public function get_layout_class();
 
    //! Type of module
    public function module_type()
    {
        return 'theme';
    }
    
    //! Initialize theme
    public function init()
    {
        $theme_class = $this->get_layout_class();
        if (!eval("return isset($theme_class::\$theme_nickname);"))
            throw new RuntimeException('Theme Layout class must have static property "theme_nickname"!');
        Layout::assign('default', $theme_class);
    }
    
    public function on_save_config()
    {
        if (GConfig::get_instance()->site->theme == $this->config_nickname())
            CM5_Core::get_instance()->invalidate_page_cache();
    }
}

?>
