<?php


class CMS_Core
{
    final private function __construct()
    {
    }
    static private $instance = null;
    
    static public function get_instance()
    {
        if (self::$instance == null)
            self::$instance = new CMS_Core();
        return self::$instance;
    }
    
    public function serve($url = null)
    {
        if ($url === null)
            $url = $_SERVER['PATH_INFO'];
            
        $p = Page::open_query()
            ->where('slug = ?')
            ->limit(1)
            ->execute($url);

        if (!$p)
            not_found();

        etag('h1', $p[0]->title);
        etag('div html_escape_off', $p[0]->body);
    }
}
?>
