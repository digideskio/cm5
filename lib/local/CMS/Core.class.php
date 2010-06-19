<?php

//! CMS Core componment
class CMS_Core
{
    //! Events dispatcher with all core events
    protected $events = null;
    
    //! An array with all modules
    protected $modules = array();
    
    //! Cache engine
    protected $cache = null;
    
    //! Construct core
    final private function __construct(Cache $cache)
    {
        // Create events
        $this->events = new EventDispatcher(array(
            'page.pre-render',
            'page.post-render',
            'page.cache-delete',
            'page.request',
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
    
    //! Invalidate all cms cache
    public function invalidate_page_cache($page)
    {
        $this->cache->delete_all();
        $this->events()->notify('page.cache-delete', array('page' => $page));
    }
    
    //! Scan modules folder and load them all
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
    
    //! Register a module
    public function register_module(CMS_Module $module)
    {   
        $minfo = $module->info();
        $this->modules[$minfo['nickname']] = $module;
        $module->init();
    }
    
    //! Enumerate modules    
    public function modules()
    {
        return $this->modules;
    }
    
    //! Get the EventDispatcher object
    public function events()
    {
        return $this->events;
    }
    
    //! Pointer to singleton instance
    static private $instance = null;
    
    
    //! Initialize the CMS core
    static public function init(Cache $cache_engine)
    {        
        // Create instance
        self::$instance = new CMS_Core($cache_engine);
        
        // Initialize modules
        self::$instance->load_modules();
    }
    
    //! Get the running instance of core
    static public function get_instance()
    {
        if (self::$instance == null)
            throw new RuntimeException('You must run Core::init() before using CMS');
        return self::$instance;
    }
    
    //! Serve a url request to CMS
    public function serve($url = null)
    {
        if ($url === null)
            $url = substr((isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:''), 1);

        // Check cache first
        $response = $this->cache->get('url-' . $url, $succ);
        if ($succ)
        {
            echo $response;
            exit;
        }
        
        // Dispatch page request to modules
        $cancel = false;
        $this->events->filter('page.request', $cancel, array('url' => $url));
        if ($cancel)
            exit;
                
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
        
        // Add cache hook
        $cache = $this->cache;
        Layout::open('default')->events()->connect('post-flush', function($e) use($cache, $url){
            $cache->set('url-' . $url, $e->arguments['layout']->get_document()->render(), 600);
        });
    }
}
?>
