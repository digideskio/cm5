<?php


class CMS_Core
{
    protected $events = null;
    
    protected $modules = array();
    
    protected $cache = null;
    
    final private function __construct(Cache $cache)
    {
        // Create events
        $this->events = new EventDispatcher(array(
            'page.pre-render',
            'page.post-render',
            'page.cache-delete',
        ));
        
        // Save caching engine
        $this->cache = $cache;
        
        // Register page chages to invalidate cache
        $cb = array($this, 'invalidate_page_cache');
        Page::events()->connect('op.post.save', function($e) use($cb){
            call_user_func($cb, $e->arguments['record']);
        });
        Page::events()->connect('op.post.delete', function($e) use($cb){
            call_user_func($cb, $e->arguments['record']);
        });
        Page::events()->connect('op.post.create', function($e) use($cb){
            call_user_func($cb, $e->arguments['record']);
        });
    }
    
    public function invalidate_page_cache($page)
    {
        $this->cache->delete_all();
    }
    
    public function load_modules()
    {
        // Load modules
        $modules_folder = dirname(__FILE__) . '/../../../modules';
        if ($dh = opendir($modules_folder))
        {
            while (($folder = readdir($dh)) !== false)
            {
                if (($folder == '.') || ($folder == '..') || (!is_dir($modules_folder . '/' . $folder)))
                    continue;
                    
                if (is_file($modules_folder . '/' . $folder . '/module.php'))
                    require_once($modules_folder . '/' . $folder . '/module.php');
            }
            closedir($dh);
        }
    }
    
    public function register_module(CMS_Module $module)
    {
        $this->modules[] = $module;
        $module->init();
    }
    
    public function modules()
    {
        return $this->modules;
    }
    
    public function events()
    {
        return $this->events;
    }
    
    static private $instance = null;
    
    
    static public function init(Cache $cache_engine)
    {
        /*$core = $cache_engine->get('core', $succ);
        if ($succ)
        {
            error_log('core cache hit');
            self::$instance = $core;
            return;
        }*/
        
        // Create instance
        self::$instance = new CMS_Core($cache_engine);
        
        // Initialize modules
        self::$instance->load_modules();
        
        // Cache core
        //$cache_engine->set('core', self::$instance);
    }
    
    static public function get_instance()
    {
        if (self::$instance == null)
            throw new RuntimeException('You must run Core::init() before using CMS');
        return self::$instance;
    }
    
    public function serve($url = null)
    {
        if ($url === null)
            $url = substr($_SERVER['PATH_INFO'], 1);

        $response = $this->cache->get('url-' . $url, $succ);
        if ($succ)
        {
            error_log('cache hit');
            echo $response;
            exit;
        }
                
        Layout::open('default')->activate();    
        $p = Page::open_query()
            ->where('slug = ?')
            ->limit(1)
            ->execute($url);

        if (!$p)
            not_found();

        $this->events->filter('page.pre-render', $p[0]);
        etag('h1', $p[0]->title);
        etag('div html_escape_off', $p[0]->body);
        $this->events->filter('page.post-render', $p[0]);
        
        // Add cache hook
        $cache = $this->cache;
        Layout::open('default')->events()->connect('post-flush', function($e) use($cache, $url){
            $cache->set('url-' . $url, $e->arguments['layout']->get_document()->render());
        });
    }
}
?>
