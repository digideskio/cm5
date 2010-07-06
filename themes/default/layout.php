<?php

class DefaultThemeLayout extends Layout{
    private $mainmenu = null;
    
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

            $sublink = $parent_link->create_link($p['title'], $p['uri']);
            if ($p['uri'] === '/')
                $sublink->set_autoselect_mode('equal');
                
            $this->__add_menu_entries($sublink, $p['childs'], $max_depth - 1);
        }
    }
    
    private function init_menu()
    {
        $this->mainmenu = new SmartMenu(array('class' => 'menu'));        
        $this->__add_menu_entries($this->mainmenu, CMS_Core::get_instance()->get_tree(), 2);
        
        // Append menu
        $this->get_document()->get_body()->getElementById("main-menu")
                ->append($this->mainmenu->render());
    }
    
    protected function __init_layout()
    {   
        $this->activate();
        $doc = $this->get_document();    
        $this->get_document()->title = Config::get('site.title');
        $this->get_document()->add_ref_css(surl('/themes/default/css/default.css'));
        
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

        // Search widget
        $this->init_menu();
        $this->deactivate();
    }
}

?>
