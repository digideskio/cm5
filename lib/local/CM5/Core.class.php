<?php

//! CMS Core componment
class CM5_Core
{

    //! Version of CMS Engine
    private $version = array(0, 9, 11);
    
    //! Events dispatcher with all core events
    protected $events = null;
    
    //! An array with all modules
    protected $modules = array();

    //! A flag if themes have been loaded
    protected $modules_loaded = false;
        
    //! A flag if themes have been loaded
    protected $themes_loaded = false;
    
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
            'page.request'
        ));
        
        // Save caching engine
        $this->cache = $cache;
        
        // Register page changes to invalidate cache
        $invalidate_func = create_function('$e',
        '   CM5_Core::get_instance()->invalidate_page_cache($e->arguments[\'record\']);   '
        );
        Page::events()->connect('op.post.save', $invalidate_func);
        Page::events()->connect('op.post.delete', $invalidate_func);
        Page::events()->connect('op.post.create', $invalidate_func);
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
        $this->modules_loaded = true;
        
        // Load modules
        $modules_folder = dirname(__FILE__) . '/../../../modules';
        if ($dh = opendir($modules_folder))
        {
            while (($file = readdir($dh)) !== false)
            {
                if (($file == '.') || ($file == '..') || (!is_dir($modules_folder . '/' . $file)))
                    continue;
                    
                if (is_file($modules_folder . '/' . $file . '/module.php'))
                    require_once($modules_folder . '/' . $file . '/module.php');
            }
            closedir($dh);
        }
    }
    
    //! Scan modules folder and load them all
    public function load_themes()
    {
        $this->themes_loaded = true;
        
        // Load themes
        $themes_folder = dirname(__FILE__) . '/../../../themes';
        if ($dh = opendir($themes_folder))
        {
            while (($file = readdir($dh)) !== false)
            {
                if (($file == '.') || ($file == '..') || (!is_dir($themes_folder . '/' . $file)))
                    continue;
                    
                if (is_file($themes_folder . '/' . $file . '/theme.php'))
                    require_once($themes_folder . '/' . $file . '/theme.php');
            }
            closedir($dh);
        }
        
        // Initialize selected theme
        $this->modules[GConfig::get_instance()->site->theme]->init();
    }
    
    //! Register a module
    /**
     * @param $module The instance of the module to register
     */
    public function register_module(CM5_Module $module)
    {   
        $minfo = $module->info();
        $this->modules[$minfo['nickname']] = $module;
        if ($module->is_enabled())
            $module->init();
    }
   
    //! Enumerate modules
    /**
     * Get the list with all registered modules
     */
    public function modules()
    {
        if (!$this->modules_loaded)
            $this->load_modules();
        return $this->modules;
    }
    
    //! Enumerate themes
    /**
     * Get the list with all registred themes
     */
    public function theme_modules()
    {
        if (!$this->themes_loaded)
            $this->load_themes();
        return array_filter($this->modules, create_function('$e', ' return ($e->module_type() == "theme"); '));
    }
    
    //! Get a module info
    public function get_module($nickname)
    {
        if (!$this->modules_loaded)
            $this->load_modules();
        if (!isset($this->modules[$nickname]))
            return null;
        return $this->modules[$nickname];
    }

    //! Enable module
    public function enable_module($nickname)
    {
        if (($m = $this->get_module($nickname)) == null)
            return false;
        
        $conf = GConfig::get_writable_copy();
        $conf->enabled_modules = implode(',',
            array_merge(
                array($nickname),
                explode(',', $conf->enabled_modules)
            )
        );
        GConfig::update($conf);
        CM5_Logger::get_instance()->notice("Module \"{$m->info_property('title')}\" has been enabled.");
        
        // Reset cache as a module may leave trash
        $this->invalidate_page_cache(null);
    }
    
    //! Disable module
    public function disable_module($nickname)
    {
        if (($m = $this->get_module($nickname)) == null)
            return false;
        
        $conf = GConfig::get_writable_copy();
        $conf->enabled_modules = implode(',',
            array_diff(
                explode(',', $conf->enabled_modules),
                array($nickname)
            )
        );
        GConfig::update($conf);
        CM5_Logger::get_instance()->notice("Module \"{$m->info_property('title')}\" has been disabled.");
        
        // Reset cache as a module may leave trash
        $this->invalidate_page_cache(null);
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
        self::$instance = new CM5_Core($cache_engine);
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
                    $parent['childs'] = array( & $p);
                else
                    $parent['childs'][] = & $p;
            }
        }
        
        // Save to cache
        $this->cache->set('pages-tree', $pages);
        return $pages;
    }
    
    private function find_page_in_tree($page_id, & $root)
    {
        if ($root['id'] == $page_id)
            return $root;
        foreach($root['childs'] as $subpage)
        {
            $ret = $this->find_page_in_tree($page_id, $subpage);
            if ($ret !== null)
                return $ret;
        }
        return null;
            
    }
    
    //! Get a page sub tree
    public function get_subtree($page_id)
    {
        $tree = $this->get_tree();
        foreach($tree as $page)
        {
            $ret = $this->find_page_in_tree($page_id, $page);
            if ($ret !== null)
                return $ret;
        }
        return null;
        
        
    }
    //! Serve a url request to CMS
    /**
     * @param $url Force a custom url to server, or null if it is auto detected.
     */
    public function serve($url = null)
    {
        if ($url === null)
            $url = (isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'/');
        CM5_Logger::get_instance()->debug('Serving web page ' . $url);
        
        // Check cache first for response
        $response = $this->cache->get('url-' . $url, $succ);
        if ($succ)
        {
            $response->show();
            exit;
        }
        
        // Initialize modules
        self::$instance->load_modules();

        // Initialize themes
        self::$instance->load_themes();
        
        $response = new CM5_Response();
                
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
            Layout::open('default')->get_document()->title = $p->title . ' | ' . GConfig::get_instance()->site->title;
            etag('div class="article"',
                tag('h1 class="title"', $p->title),
                tag('div html_escape_off', $p->body)
            );
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
