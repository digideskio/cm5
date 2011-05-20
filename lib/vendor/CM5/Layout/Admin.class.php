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
 * Layout for admin interface.
 */
class CM5_Layout_Admin extends Layout
{
    private $mainmenu = null;

    private $submenu = null;
    
    public function getMainMenu()
    {
        return $this->mainmenu;
    }

    private function initializeMenu()
    {
        $this->mainmenu = new SmartMenu(array('class' => 'menu'));
        $this->events()->connect('pre-flush', function($event) {
            $layout = $event->arguments['layout'];
            $layout->getDocument()->get_body()->getElementById('main-menu')
                ->append($layout->getMainMenu()->render());
        });

        if (Authz::is_allowed('page', 'admin'))
            $this->mainmenu->create_link('Pages', url('/admin/editor'));
        if (Authz::is_allowed('file', 'admin'))
            $this->mainmenu->create_link('Files', url('/admin/files'));
        if (Authz::is_allowed('module', 'admin'))
            $this->mainmenu->create_link('Modules', url('/admin/modules'));
        if (Authz::is_allowed('theme', 'admin'))
            $this->mainmenu->create_link('Themes', url('/admin/themes'));
        if (Authz::is_allowed('user', 'admin'))
            $this->mainmenu->create_link('Users', url('/admin/users'));
        if (Authz::is_allowed('log', 'view'))
            $this->mainmenu->create_link('Log', url('/admin/log'));
        if (Authz::is_allowed('system.settings', 'admin'))
            $this->mainmenu->create_link('Settings', url('/admin/settings'));
    }
    
    public function getSubmenu()
    {
        if ($this->submenu !== null)
            return $this->submenu;

        $this->submenu = new SmartMenu(array('class' => 'menu submenu'));
        $this->events()->connect('pre-flush', function($event) {
            $layout = $event->arguments['layout'];
            $layout->getDocument()->get_body()->getElementById('content')
                ->prepend($layout->getSubmenu()->render());
        });;
        
        return $this->submenu;
    }
    
    protected function onInitialize()
    {   
        $doc = $this->getDocument();    
        $this->getDocument()->title = CM5_Config::getInstance()->site->title . ' | Admin panel';
        $this->getDocument()->add_ref_css(surl('/static/css/admin.css'));
        $this->getDocument()->add_ref_js(surl('/static/js/jquery-1.4.4.min.js'));
        $this->getDocument()->add_ref_js(surl('/static/js/jquery-ui-1.8.2.custom.min.js'));
        $this->getDocument()->add_ref_js(surl('/static/js/jquery.ba-resize.min.js'));
        //$this->getDocument()->add_ref_js(surl('/static/js/jscolor.js.php'));
        $this->getDocument()->add_ref_css(surl('/static/js/jqMiniColors/jquery.miniColors.css'));
        $this->getDocument()->add_ref_js(surl('/static/js/jqMiniColors/jquery.miniColors.min.js'));

        $this->activateSlot();
        etag('div id="wrapper"')->push_parent();
        etag('div id="header"',
            tag('h1', 
                tag('a target="_blank"', CM5_Config::getInstance()->site->title)->attr('href', url('/')), tag('span', 'admin panel')),
            $loginfo = tag('div id="login-info"'),
            tag('div id="main-menu"')
        );
        etag('div id="main"',
            $def_content = 
            tag('div id="content"')
        );
        
        $version = CM5_Core::getInstance()->getVersion();
        etag('div id="footer"', 
            tag('ul',
                tag('li',
                    tag('a', "CM5 v{$version[0]}.{$version[1]}.{$version[2]}",
                        array('href' => 'http://code.0x0lab.org/p/cm5', 'target' => '_blank'))
                ),
                tag('li',
                    'made with ', tag('a', 'PHPlibs',
                        array('href' => 'http://phplibs.kmfa.net', 'target' => '_blank'))
                )
            )
        );
        etag('script html_escape_off', '
        	$(document).ready(function(){
        		$("input[type=color]").miniColors();
        	});
        ');
        
        $this->setSlot('default', $def_content);

        // Initialize login info        
        if (Authn_Realm::has_identity())
        {
            if ($_SERVER['QUERY_STRING'] == '')
                $logout_url = $_SERVER['REQUEST_URI'] . "/+logout";
            else
                $logout_url = url($_SERVER['PATH_INFO'] . "/+logout");
            $loginfo->append(
                tag('a class="user"', Authn_Realm::get_identity()->id())->attr('href', (string)UrlFactory::craft('user.me')), ' ',
                tag('a', 'logout', array('href' => $logout_url))
            );
        }
        $this->initializeMenu();
    }
}
