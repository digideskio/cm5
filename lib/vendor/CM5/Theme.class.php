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
    abstract public function getLayoutClass();
    
    /**
     * Get the instance of the getLayoutClass()
     * @return Layout
     */
	public function getLayout()
	{
		$class_name = $this->getLayoutClass();
		return $class_name::getInstance();
	}
	
    /**
     * (non-PHPdoc)
     * @see CM5_Module::getModuleType()
     */
    public function getModuleType()
    {
        return 'theme';
    }
    
    /**
     * Automatic initialization of the theme
     * (non-PHPdoc)
     * @see CM5_Module::onInitialize()
     */
    public function onInitialize()
    {
        $theme_class = $this->getLayoutClass();
        if (!isset($theme_class::$theme_nickname))
            throw new RuntimeException('Theme Layout class must have static property "theme_nickname"!');
        $theme_class::getInstance();
    }
    
    /**
     * Usually when theme settings are changed, cache must be invalidated.
     */
    public function onSaveConfig()
    {
        if (CM5_Config::getInstance()->site->theme == $this->getConfigNickname())
            CM5_Core::getInstance()->invalidatePageCache(null);
    }
}

