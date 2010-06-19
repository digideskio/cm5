<?php

class CMS_Module_Sitemap implements CMS_Module
{
    //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'sitemap',
            'title' => 'Search Engine Optimizations',
            'description' => 'Generates and servers /sitemap.xml to provide information about content of cms.'
        );
    }
    
    //! Initialize module
    public function init()
    {
        $c = CMS_Core::get_instance();
        $c->events()->connect('page.request', array($this, 'event_page_request'));
    }
    
    public function generate_sitemap()
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>";
        $urlset = tag('urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '.
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
        )->attr('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 '.
			'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        
        $pages = Page::open_query()->where('status = ?')->execute('published');
        foreach ($pages as $p)
        {
            tag('url',
                tag('loc', (string)UrlFactory::craft('page.view', $p)),
                tag('lastmod', gmdate(DATE_ISO8601, $p->lastmodified->format('U'))),
                tag('changefreq', 'weekly')
            )->appendto($urlset);
        }
        return $xml . $urlset;

    }
    public function event_page_request($event)
    {
        if ($event->arguments['url'] != 'sitemap.xml')
            return;

        $event->filtered_value = true;
        header('Content-Type: text/xml');
        echo $this->generate_sitemap();
    }
}

CMS_Core::get_instance()->register_module(new CMS_Module_Sitemap());
?>
