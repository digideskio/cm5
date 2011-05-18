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

require_once(__DIR__ . '/layout.php');

class DefaultTheme extends CM5_Theme
{
    public function onRequestMetaInfo()
    {
        return array(
            'nickname' => 'default',
            'title' => 'Default theme',
            'description' => 'Default theme that comes with CMS.'
        );
    }
    
    public function getDefaultConfiguration()
    {
        return array(
            'page-background-color' => '#4B484F',
            'article-background-color' => '#F6F5FF',
            'article-text-color' => '#292929',
            'menu-background-color' => '#F0F0F0',
            'menu-text-color' => '#0A0A0A',
            'menu-selected-background-color' => '#D20000',
            'menu-selected-text-color' => '#FFFFFF',
            'footer' => "using <a target=\"_blank\" href=\"http://code.0x0lab.org/p/cm5\">CM5</a>",
            'favicon-url' => '',
            'extra-css' => '',
        );
    }
    
    public function getConfigurableFields()
    {
        return array(
            'page-background-color' => array('display' => 'Page background color:', 'type' => 'color'),
            'article-background-color' => array('display' => 'Article background color:', 'type' => 'color'),
            'article-text-color' => array('display' => 'Article text color:', 'type' => 'color'),
            'menu-background-color' => array('display' => 'Menu background color:', 'type' => 'color'),
            'menu-text-color' => array('display' => 'Menu text color:', 'type' => 'color'),
            'menu-selected-background-color' => array('display' => 'Menu selected background color:', 'type' => 'color'),
            'menu-selected-text-color' => array('display' => 'Menu selected text color:', 'type' => 'color'),
            'favicon-url' => array('display' => 'Favicon url:'),
            'footer' => array('display' => 'Footer content:', 'type' => 'textarea'),
            'extra-css' => array('display' => 'Extra css to be included:', 'type' => 'textarea')
        );
    }
    public function getLayoutClass()
    {
        return 'DefaultThemeLayout';
    }
}

DefaultTheme::register();
