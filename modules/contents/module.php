<?php

class CMS_Module_Contents implements CMS_Module
{
    //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'contents',
            'title' => 'Contents generator',
            'description' => 'Provides a way to add a content list for subpages in any page'
        );
    }
    
    //! Initialize module
    public function init()
    {
        $c = CMS_Core::get_instance();
        $c->events()->connect('page.pre-render', array($this, 'event_pre_render'));
    }
    
    public function event_pre_render($event)
    {
        $p = $event->filtered_value;
        if (strstr($p->body, '##contents##') === false)
            return;
        
        // Create contents index
        $subpages = Page::open_query()->where('status = ?')->where('parent_id = ?')->execute('published', $p->id);
        
        $contents_el = tag('div class="contents"', $ul = tag('ul'));
        foreach($subpages as $sp)
            $ul->append(tag('li', UrlFactory::craft('page.view', $sp)->anchor($sp->title)));
            
        $p->body = str_replace('##contents##', (string)$contents_el, $p->body);
    }
}

CMS_Core::get_instance()->register_module(new CMS_Module_Contents());
?>
