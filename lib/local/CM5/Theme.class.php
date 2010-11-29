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
 * Interface that must be followed by themes
 * 
 * This will be typically used by themes.
 * @author sque
 *
 */
abstract class CM5_Theme extends CM5_Module
{
    /**
     * Return the CM5_ThemeLayout class
     * @return string The name of the class.
     */
    abstract public function get_layout_class();
 
    public function module_type()
    {
        return 'theme';
    }
    
    /**
     * Automatic initialization of the theme
     * (non-PHPdoc)
     * @see lib/local/CM5/CM5_Module::init()
     */
    public function init()
    {
        $theme_class = $this->get_layout_class();
        if (!eval("return isset($theme_class::\$theme_nickname);"))
            throw new RuntimeException('Theme Layout class must have static property "theme_nickname"!');
        Layout::assign('default', $theme_class);
    }
    
    /**
     * Usually when theme settings are changed, cache must be invalidated.
     */
    public function on_save_config()
    {
        if (GConfig::get_instance()->site->theme == $this->config_nickname())
            CM5_Core::get_instance()->invalidate_page_cache(null);
    }
}

