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

class DefaultThemeLayout extends CM5_ThemeLayout
{
    private $mainmenu = null;
    
    public static $theme_nickname = 'default';
    
    public function get_mainmenu()
    {
        return $this->mainmenu;
    }

    private function __add_menu_entries($parent_link, $childs, $max_depth)
    {
        if ($max_depth <= 0)
            return;
            
        foreach($childs as $p)
        {
            if ($p['status'] !== 'published')
                continue;

            $sublink = $parent_link->create_link($p['title'], url($p['uri']));
            if ($p['uri'] === '/')
                $sublink->set_autoselect_mode('equal');
                
            $this->__add_menu_entries($sublink, $p['children'], $max_depth - 1);
        }
    }
    
    private function init_menu()
    {
        $this->mainmenu = new SmartMenu(array('class' => 'menu'));        
        $this->__add_menu_entries($this->mainmenu, CM5_Core::get_instance()->get_tree(), 2);
        
        // Append menu
        $this->get_document()->get_body()->getElementById("main-menu")
                ->append($this->mainmenu->render());
    }
    
    protected function __init_layout()
    {   
        $this->activate();
        $doc = $this->get_document();    
        $this->get_document()->title = GConfig::get_instance()->site->title;
        $this->get_document()->add_ref_css(surl('/themes/default/css/default.css'));
        
        if ($this->get_config()->{"favicon-url"})
            $this->get_document()->add_favicon($this->get_config()->{"favicon-url"});
        
        etag('div id="wrapper"')->push_parent();
        etag('div id="header"',
            tag('h1', GConfig::get_instance()->site->title),
            tag('div id="main-menu"')
        );
        etag('div id="main"',
            $def_content = 
            tag('div id="content"'),
            tag('div id="spacer"')
        );
        etag('div id="footer" html_escape_off', 
            $this->get_config()->footer
        );
        $this->set_default_container($def_content);
        
        // Customized style
        $this->get_document()->get_head()->append(tag('style type="text/css" html_escape_off',"
            body{
                background-color: #{$this->get_config()->{"page-background-color"}};
            }
            #content{
                background-color: #{$this->get_config()->{"article-background-color"}};
                color: #{$this->get_config()->{"article-text-color"}};
            }
            .menu >li{
                background-color: #{$this->get_config()->{"menu-background-color"}};
            }
            .menu > li a{
                color: #{$this->get_config()->{"menu-text-color"}};
            }
            .menu li.selected a{
                color: #{$this->get_config()->{"menu-selected-text-color"}};
            }
            .menu{
                border-bottom-color: #{$this->get_config()->{"menu-selected-background-color"}};
             }
             
            .menu > li.selected, .menu > li.selected ul{
                background-color: #{$this->get_config()->{"menu-selected-background-color"}};
            }
            "));

        if ($this->get_config()->{"extra-css"})
            $this->get_document()->get_head()->append(tag('style type="text/css" html_escape_off',$this->get_config()->{"extra-css"}));
            
        // Search widget
        $this->init_menu();
        $this->deactivate();
    }
}

?>
