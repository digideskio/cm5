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
        $add_menu_entries = function($parent_link, $parent_page = null) use(&$add_menu_entries)
        {
            $q = Page::open_query()->where('status = ?')->push_exec_param('published');
            if ($parent_page === null)
                $subpages = $q->where('parent_id is null')->execute();
            else
                $subpages = $q->where('parent_id = ?')->execute($parent_page->id);
                
            foreach($subpages as $p)
            {
                $sublink = $parent_link->create_link($p->title, $p->full_path());
                $add_menu_entries($sublink, $p);
            }
        };
        
        $add_menu_entries($this->mainmenu);
        
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
            tag('div id="content"')
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
