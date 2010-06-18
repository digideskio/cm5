<?php

class CMS_Module_Sitemap implements CMS_Module
{
    //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'sitemap',
            'title' => 'Search engines sitemap xml',
            'description' => 'Servers /sitemap.xml and provide information about pages of cms.'
        );
    }
    
    //! Initialize module
    public function init()
    {
        $c = CMS_Core::get_instance();
        $c->events()->connect('page.request', array($this, 'event_page_request'));
    }
    
    public function event_page_request($event, & $page)
    {
        var_dump($page);
    }
}

CMS_Core::get_instance()->register_module(new CMS_Module_Sitemap());
?>
