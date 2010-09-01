<?php

class CM5_Module_SEO extends CM5_Module
{
    //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'seo',
            'title' => 'Search Engine Optimizations',
            'description' => 'Generates and servers /sitemap.xml and /robots.txt to provide information about content of cms.'
        );
    }
    
    //! Initialize module
    public function init()
    {
        $c = CM5_Core::get_instance();
        $c->events()->connect('page.request', array($this, 'event_page_request'));
    }
    
    public function generate_sitemap()
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . 
            (empty($_SERVER['HTTPS'])?'http':'https') .'://' . $_SERVER['HTTP_HOST'] . url('/sitemap.xsl') . '"?>';

        $urlset = tag('urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '.
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
        )->attr('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 '.
			'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        
        $pages = Page::open_query()->where('status = ?')->execute('published');
        foreach ($pages as $p)
        {
            tag('url',
                tag('loc', (string)UrlFactory::craft_fqn('page.view', $p)),
                tag('lastmod', gmdate('Y-m-d\TH:i:s+00:00', $p->lastmodified->format('U'))),
                tag('priority', '0.5'),
                tag('changefreq', 'weekly')
            )->appendto($urlset);
        }
        return $xml . $urlset;
    }
    
    public function generate_robots()
    {
        return "User-Agent: *\n" .
            "Allow: /\n" .
        	"Disallow: /admin\n" .
        	"Disallow: /modules\n" .
        	"Disallow: /themes\n" .
        	"Disallow: /static\n" .
        	"Disallow: /lib\n" .
        	"Disallow: /web\n" .
            'Sitemap: ' . (empty($_SERVER['HTTPS'])?'http':'https') .'://' . $_SERVER['HTTP_HOST'] . url('/sitemap.xml');
    }
    
    public function event_page_request($event)
    {
        $response = $event->arguments['response'];
        if ($event->arguments['url'] == '/sitemap.xml')
        {
            $event->filtered_value = true;
            $response->add_header('Content-Type: text/xml');
            $response->document = $this->generate_sitemap();
        }
        else if ($event->arguments['url'] == '/sitemap.xsl')
        {
            $event->filtered_value = true;
            $response->add_header('Content-Type: text/xml');
            $response->document = file_get_contents(dirname(__FILE__) . '/sitemap.xsl');
        }
        else if ($event->arguments['url'] == '/robots.txt')
        {
            $event->filtered_value = true;
            $response->add_header('Content-Type: text/plain');
            $response->document = $this->generate_robots();
        }
    }
}

CM5_Module_SEO::register();
?>
