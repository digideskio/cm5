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


class Layout_Admin extends Layout
{
    private $mainmenu = null;

    private $submenu = null;
    
    public function get_mainmenu()
    {
        return $this->mainmenu;
    }

    private function init_menu()
    {
        $this->mainmenu = new SmartMenu(array('class' => 'menu'));
        $this->events()->connect('pre-flush', create_function('$event',
        '
            $layout = $event->arguments["layout"];
            $layout->get_document()->get_body()->getElementById("main-menu")
                ->append($layout->get_mainmenu()->render());
        '));

        if (Authz::is_allowed('page', 'admin'))
            $this->mainmenu->create_link('Pages', url('/admin/pages'));
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
    
    public function get_submenu()
    {
        if ($this->submenu !== null)
            return $this->submenu;

        $this->submenu = new SmartMenu(array('class' => 'menu submenu'));
        $this->events()->connect('pre-flush', create_function('$event',
        '
            $layout = $event->arguments["layout"];
            $layout->get_document()->get_body()->getElementById("content")
                ->prepend($layout->get_submenu()->render());
        '));
        
        return $this->submenu;
    }
    
    protected function __init_layout()
    {   
        $this->activate();
        $doc = $this->get_document();    
        $this->get_document()->title = GConfig::get_instance()->site->title . ' | Admin panel';
        $this->get_document()->add_ref_css(surl('/static/css/admin.css'));
        $this->get_document()->add_ref_js(surl('/static/js/jquery-1.4.2.min.js'));
        $this->get_document()->add_ref_js(surl('/static/js/jquery-ui-1.8.2.custom.min.js'));
        $this->get_document()->add_ref_js(surl('/static/js/jquery.ba-resize.min.js'));
        $this->get_document()->add_ref_js(surl('/static/js/jscolor.js.php'));

        etag('div id="wrapper"')->push_parent();
        etag('div id="header"',
            tag('h1', 
                tag('a target="_blank"', GConfig::get_instance()->site->title . ' ')->attr('href', url('/')), tag('span', 'admin panel')),
            tag('div id="login-info"'),
            tag('div id="main-menu"')
        );
        etag('div id="main"',
            $def_content = 
            tag('div id="content"')
        );
        
        $version = CMS_Core::get_instance()->get_version();
        etag('div id="footer"', 
            tag('ul',
                tag('li',
                    tag('a', "CMS v{$version[0]}.{$version[1]}.{$version[2]}",
                        array('href' => 'http://cms.kmfa.net', 'target' => '_blank'))
                ),
                tag('li',
                    'made with ', tag('a', 'PHPlibs',
                        array('href' => 'http://phplibs.kmfa.net', 'target' => '_blank'))
                )
            )
        );
        
        $this->set_default_container($def_content);

        // Initialize login info
        $loginfo = $this->get_document()->get_body()->getElementById('login-info');
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
        // Search widgeet
        $this->init_menu();
        $this->deactivate();
    }
}
?>
