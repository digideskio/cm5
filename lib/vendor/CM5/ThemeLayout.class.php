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
 * Base class to implement theme layout
 */
class CM5_ThemeLayout extends Layout
{
    /**
     * The configuration of the module/theme
     * @var Zend_Config
     */
    private $module_config = null;
    
    /**
     * Get the configuration of this theme
     * @return Zend_Config The configuration of this theme.
     */
    public function getConfig()
    {
        if ($this->module_config !== null)
            return $this->module_config;
        return CM5_Core::getInstance()->getModule(static::$theme_nickname)->getConfig();
    }
}

