<?php

class CMS_Module_Contents implements CMS_Module
{
    //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'content-magic',
            'title' => 'Content Magic',
            'description' => 'Provides a set of magic keywords to add subpages indexing, redirection etc.'
        );
    }
    
    //! Initialize module
    public function init()
    {
        $c = CMS_Core::get_instance();
        $c->events()->connect('page.pre-render', array($this, 'event_pre_render'));
        $c->events()->connect('page.post-render', array($this, 'event_post_render'));
    }
    
    private function replace_subpages(Page $p)
    {
        if (strstr($p->body, '##subpages##') === false)
            return;
        
        // Create contents index
        $subpages = Page::open_query()->where('status = ?')->where('parent_id = ?')->execute('published', $p->id);
        
        $contents_el = tag('div class="contents"', $ul = tag('ul'));
        foreach($subpages as $sp)
            $ul->append(tag('li', UrlFactory::craft('page.view', $sp)->anchor($sp->title)));
            
        $p->body = str_replace('##subpages##', (string)$contents_el, $p->body);
    }
    
    private function execute_redirect(Page $p, CMS_Response $r)
    {
        if (strstr($p->body, '##redirect ') === false)
            return;

        if (!preg_match('/##redirect\s+(?P<url>(http)?|.+)\s*##/m', $r->document, $matches))
            return;

        if (empty($matches['url']))
            return;
                
        $r->add_header('Location: ' . $matches['url']);

    }
    
    //! Handler for pre rendering
    public function event_pre_render($event)
    {
        $p = $event->filtered_value;
        
        // Execute subpages
        $this->replace_subpages($p);
    }
    
    public function event_post_render($event)
    {
        $resp = $event->filtered_value;
        
        // Execute subpages
        $this->execute_redirect($event->arguments['page'] , $resp);
    }
}

CMS_Core::get_instance()->register_module(new CMS_Module_Contents());
?>
