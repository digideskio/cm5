<?php

//! CMS Core componment
class CMS_Core
{

    //! Version of CMS Engine
    private $version = array(0, 9, 9);
    
    //! Events dispatcher with all core events
    protected $events = null;
    
    //! An array with all modules
    protected $modules = null;
    
    //! Cache engine
    protected $cache = null;
    
    //! Construct core
    /**
     * @param $cache The caching engine to be used from CMS
     */
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
    
    //! Invalidate page cache
    /**
     * @param $page The page that gets invalidated
     */
    public function invalidate_page_cache($page)
    {
        $this->cache->delete_all();
        $this->events()->notify('page.cache-delete', array('page' => $page));
    }
    
    //! Scan modules folder and load them all
    public function load_modules()
    {
        $this->modules = array();
        
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
    /**
     * @param $module The instance of the module to register
     */
    public function register_module(CMS_Module $module)
    {   
        $minfo = $module->info();
        $this->modules[$minfo['nickname']] = $module;
        $module->init();
    }
    
    //! Enumerate modules
    /**
     * Get the list with all registered modules
     */
    public function modules()
    {
        if ($this->modules === null)
            $this->load_modules();
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
    /**
     * @param $cache_engine The cache engine to be used by the CMS.
     */
    static public function init(Cache $cache_engine)
    {        
        // Create instance
        self::$instance = new CMS_Core($cache_engine);
    }
    
    //! Get the running instance of core
    static public function get_instance()
    {
        if (self::$instance == null)
            throw new RuntimeException('You must run Core::init() before using CMS');
        return self::$instance;
    }
    
    //! Get version of CMS
    public function get_version()
    {
        return $this->version;
    }
    
    //! Get the pages tree
    public function get_tree()
    {
        // Read from cache
        $pages = $this->cache->get('pages-tree', $succ);
        if ($succ)
           return $pages;
            
        // Read from database
        $dbpages = Page::raw_query()
            ->select(array('id', 'parent_id', 'title', 'status', 'uri', 'system'))
            ->order_by('order', 'ASC')
            ->execute();

        // Reindex pages based on their parent id
        $indexed_pages = array();
        foreach($dbpages as $p)
            $indexed_pages[$p['id']] = $p;

        // Create parent/child references
        $pages = array();
        foreach($indexed_pages as $idx => & $p)
        {
            if (!isset($p['childs']))
                $p['childs'] = array();

            if ($p['parent_id'] === null)
                $pages[] = & $p;
            else
            {
                $parent = & $indexed_pages[$p['parent_id']];
                if (!isset($parent['childs']))
                    $parent['childs'] = array($p);
                else
                    $parent['childs'][] = & $p;
            }
        }

        // Save to cache
        $this->cache->set('pages-tree', $pages);
        return $pages;
    }
    
    //! Serve a url request to CMS
    /**
     * @param $url Force a custom url to server, or null if it is auto detected.
     */
    public function serve($url = null)
    {
        if ($url === null)
            $url = (isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'/');

        // Check cache first for response
        $response = $this->cache->get('url-' . $url, $succ);
        if ($succ)
        {
            $response->show();
            exit;
        }
        
        // Initialize modules
        self::$instance->load_modules();

        $response = new CMS_Response();
                
        // Dispatch page request to modules
        $stop_propagation = false;
        $this->events->filter('page.request', $stop_propagation, array('url' => $url, 'response' => $response));

        if (!$stop_propagation)
        {  
            // Check for CMS pages
            Layout::open('default')->activate();
            
            if ($url == '')
                $p = Page::open(1);   // Home page
            else
            {
                $p = Page::open_query()
                    ->where('uri = ?')
                    ->limit(1)
                    ->execute($url);

                if (!$p)
                    not_found();
                $p = $p[0];
            }
            
            $this->events->filter('page.pre-render', $p, array('url' => $url, 'response' => $response));
            etag('h1', $p->title);
            etag('div html_escape_off', $p->body);
            Layout::open('default')->deactivate();
            $response->document = Layout::open('default')->get_document()->render();

            // Trigger post render
            $this->events->filter('page.post-render', $response, array('url' => $url, 'page' => $p));
        }

        // Show response
        $response->show();
        
        // Add cache hook
        if ($response->cachable)
            $this->cache->set('url-' . $url, $response, 600);
    }
}
?>
