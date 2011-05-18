<?php
/*
 *  This file is part of PHPLibs <http://phplibs.kmfa.net/>.
 *  
 *  Copyright (c) 2010 < squarious at gmail dot com > .
 *  
 *  PHPLibs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  PHPLibs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with PHPLibs.  If not, see <http://www.gnu.org/licenses/>.
 *  
 */

/**
 * Layout for login interface.
 */
class CM5_Layout_Login extends Layout
{
    protected function onInitialize()
    {   
        $this->activateSlot();
        $doc = $this->getDocument();    
        $this->getDocument()->title = CM5_Config::getInstance()->site->title . ' | Admin panel';
        $this->getDocument()->add_ref_css(surl('/static/css/login.css'));

        etag('div id="wrapper"')->push_parent();
        etag('div id="main"',             
            $def_content = tag('div id="content"')
        );
                
        $this->setSlot('default', $def_content);

        $this->deactivate();
    }
}
