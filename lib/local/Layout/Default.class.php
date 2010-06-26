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


class Layout_Default extends Layout
{
    private $mainmenu = null;
    
    public function get_mainmenu()
    {
        return $this->mainmenu;
    }

    private function init_menu()
    {
        $this->mainmenu = new SmartMenu(array('class' => 'menu'));
        $add_entries = function($parent_link, $childs) use(&$add_entries)
        {
            foreach($childs as $p)
            {
                if ($p['status'] !== 'published')
                    continue;

                $sublink = $parent_link->create_link($p['title'], $p['uri']);
                if ($p['uri'] == '/')
                    $sublink->set_autoselect_mode('equal');
                    
                $add_entries($sublink, $p['childs']);
            }
        };
        
        $add_entries($this->mainmenu, CMS_Core::get_instance()->get_tree());
        
        // Append menu
        $this->get_document()->get_body()->getElementById("main-menu")
                ->append($this->mainmenu->render());
    }
    
    protected function __init_layout()
    {   
        $this->activate();
        $doc = $this->get_document();    
        $this->get_document()->title = Config::get('site.title');
        $this->get_document()->add_ref_css(surl('/static/css/default.css'));
        $this->get_document()->add_ref_js(surl('/static/js/jquery-1.4.2.min.js'));
        
        etag('div id="wrapper"')->push_parent();
        etag('div id="header"',
            tag('h1', Config::get('site.title')),
            tag('div id="main-menu"')
        );
        etag('div id="main"',
            $def_content = 
            tag('div id="content"'),
            tag('div id="spacer"')
        );
        etag('div id="footer"', 
            tag('a', 'PHPlibs', array('href' => 'http://phplibs.kmfa.net')),' skeleton'
        );
        $this->set_default_container($def_content);

        // Search widgeet
        $this->init_menu();
        $this->deactivate();
    }
}
?>
