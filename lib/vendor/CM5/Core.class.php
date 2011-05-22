<?php
/*
 *  This file is part of CM5 <http://code.0x0lab.org/p/cm5>.
 *  
 *  Copyright (c) 2010 Sque.
 *  
 *  CM5 is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published 
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  CM5 is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with CM5.  If not, see <http://www.gnu.org/licenses/>.
 *  
 *  Contributors:
 *      Sque - initial API and implementation
 */

/**
 * CM5 Core component
 * 
 * The Core is singleton and is used to manage 
 * all other subsystems of the CMS.
 * 
 * @author sque
 *
 */
class CM5_Core
{
    /**
     * Static version of Core
     * @var array
     */
    private $version = array(0, 11, 0);
    
    /**
     * Events dispatcher with all core events
     * @var EventDispatcher
     */
    protected $events = null;
    
	/**
	 * An array with all module
	 * @var array
	 */
    protected $modules = array();

    /**
     * A flag if themes have been loaded
     * @var boolean
     */
    protected $modules_loaded = false;
        
    /**
     * A flag if themes have been loaded
     * @var boolean
     */
    protected $themes_loaded = false;
    
    /**
     * Cache engine that will be used by CMS
     * @var Cache
     */ 
    protected $cache = null;
    
    /**
     * The working context of this 
     */
    protected $working_context = 'frontend';
    
    //! Construct core
    /**
     * @param $cache The caching engine to be used from CMS
     */
    final private function __construct(Cache $cache)
    {
        // Create events
        $this->events = new EventDispatcher(array(
        	'page.post-fetchdata',
            'page.pre-render',
            'page.post-render',
            'page.cache-delete',
            'page.request'
        ));
        
        // Save caching engine
        $this->cache = $cache;
    }
    
    /**
     * Get current working context
     */
    public function getWorkingContext()
    {
    	return $this->working_context;
    }
    
    /**
     * Switch to backend working context
     */
    public function switchBackendWorkingContext()
    {
    	if ($this->working_context !== 'backend'){
			// Register page changes to invalidate cache
        	$invalidate_func = function($e) {
        		CM5_Core::getInstance()->invalidatePageCache($e->arguments['record']);
	        };
	        CM5_Model_Page::events()->connect('op.post.save', $invalidate_func);
	        CM5_Model_Page::events()->connect('op.post.delete', $invalidate_func);
	        CM5_Model_Page::events()->connect('op.post.create', $invalidate_func);
	        
	        $this->working_context = 'backend';
    	}
    }
    
    /**
     * Invalidate cache for a specific page. This will resolve
     * all the other cached object that relay on this object
     * and must be invalidated too.
     * @param CM5_Model_Page $page The page that gets invalidated
     */
    public function invalidatePageCache($page)
    {
        $this->cache->delete_all();
        $this->events()->notify('page.cache-delete', array('page' => $page));
    }
    
    /**
     * Load modules from a folder
     */
    private function loadModulesFromFolder($modules_folder, $filename)
    {
		// Load modules
        if ($dh = opendir($modules_folder)) {
            while (($file = readdir($dh)) !== false) {
                if (($file[0] == '.') || (!is_dir($modules_folder . '/' . $file)))
                    continue;
                
                if (is_file($modules_folder . '/' . $file . '/' . $filename)) {
                    $module_i = require_once($modules_folder . '/' . $file . '/' . $filename);
                    if (!isset($module_i['class'], $module_i['nickname'])) {
                    	CM5_Logger::getInstance()->err('Error loading module "' . $file . '". ' . $filename . ' did not return properly.');
                    	continue;
                    }
                    $module = new $module_i['class']($module_i['nickname'], $module_i);
                    $this->registerModule($module);                    
                }
            }
            closedir($dh);
        }    	
    }
    /**
     * Scan modules folder and load them all
     */
    public function loadModules()
    {
        $this->modules_loaded = true;
        $this->loadModulesFromFolder(__DIR__ . '/../../../modules', 'module.php');
    }
    
    /**
     * Scan modules folder and load them all
     */
    public function loadThemes()
    {
        $this->themes_loaded = true;
        $this->loadModulesFromFolder(__DIR__ . '/../../../themes', 'theme.php');
    }
    
    /**
     * Get frontend theme
     * @return CM5_Theme
     */
    public function getSelectedTheme()
    {
    	return $this->modules[CM5_Config::getInstance()->site->theme];
    }
    
    /**
     * Register a module at the core.
     * @param CM5_Module $module The instance of the module to register
     */
    public function registerModule(CM5_Module $module)
    {   
        $minfo = $module->getMetaInfo();
        $this->modules[$minfo['nickname']] = $module;
        if ($module->isEnabled())
        	$module->initialize($this->working_context);
    }
   
    /**
     * Get the list with all registered modules
     * @return array Array of loaded modules.
     */
    public function getModules()
    {
        if (!$this->modules_loaded)
            $this->loadModules();
        return $this->modules;
    }
    
    /**
     * Get the list with all registred themes
     * @return array Array of loaded themes. 
     */
    public function getThemeModules()
    {
        if (!$this->themes_loaded)
            $this->loadThemes();
        return array_filter($this->modules, function($e) { return ($e->getModuleType() == "theme"); });
    }
    
    /**
     * Get a module info
     * @param string $nickname The nickname of the module
     * @return CM5_Module The module or NULL if it is not found.
     */
    public function getModule($nickname)
    {
        if (!$this->modules_loaded)
            $this->loadModules();
        if (!isset($this->modules[$nickname]))
            return null;
        return $this->modules[$nickname];
    }

    /**
     * Enable module
     * @param string $nickname The nickname of the module
     */
    public function enableModule($nickname)
    {
        if (($m = $this->getModule($nickname)) == null)
            return false;

        if ($m->onEnable() === false) {
        	CM5_Logger::getInstance()->err("Module \"{$m->getMetaInfoEntry('title')}\" could not be enabled.");
        	return false;
        }
        
        $conf = CM5_Config::getWritableCopy();
        $conf->enabled_modules = implode(',',
            array_merge(
                array($nickname),
                explode(',', $conf->enabled_modules)
            )
        );
        CM5_Config::update($conf);        
        CM5_Logger::getInstance()->notice("Module \"{$m->getMetaInfoEntry('title')}\" has been enabled.");
        
        // Reset cache as a module mayisenabled leave trash
        $this->invalidatePageCache(null);
    }
    
    /**
     * Disable module
     * @param string $nickname The nickname of the module
     */
    public function disableModule($nickname)
    {
        if (($m = $this->getModule($nickname)) == null)
            return false;
        
        $m->onDisable();
            
        $conf = CM5_Config::getWritableCopy();
        $conf->enabled_modules = implode(',',
            array_diff(
                explode(',', $conf->enabled_modules),
                array($nickname)
            )
        );
        CM5_Config::update($conf);        
        CM5_Logger::getInstance()->notice("Module \"{$m->getMetaInfoEntry('title')}\" has been disabled.");
        
        // Reset cache as a module may leave trash
        $this->invalidatePageCache(null);
    }
    
    /**
     * Get the dispatcher for the events emmited by the core.
     * @return EventDispatcher Supported events are:
     * - page.request: Filter when request comes and no decision has been taken.
     * - page.post-fetchdata: Filter on page after it was fetched from database.
     * - page.pre-render: Filter before rendering a page
     * - page.post-render: Filter after rendering the page
	 * - page.cache-delete: Notify that the cache of a page has been invalidated.
     */
    public function events()
    {
        return $this->events;
    }
    
    /**
     * Pointer to singleton instance
     * @var CM5_Core
     */
    static private $instance = null;
    
    /**
     * Initialize the CMS core
     * @param Cache $cache_engine The cache engine to be used by the CMS.
     * @return CM5_Core The actual initialized instance.
     */
    static public function initialize(Cache $cache_engine)
    {        
        // Create instance
        return self::$instance = new CM5_Core($cache_engine);
    }
    
    /**
     * Get the running instance of core
     * @return CM5_Core The actual singleton instance.
     */
    static public function getInstance()
    {
        if (self::$instance == null)
            throw new RuntimeException('You must run Core::init() before using CMS');
        return self::$instance;
    }
    
    /**
     * Get version of CMS
     * @return array Associative array with all parts of version
     */ 
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * Iterate pages and generate the tree of pages.
     * This action is automatically cached.
     * @return array Associative array with all pages starting from root level. 
     */ 
    public function getTree()
    {
        // Read from cache
        $pages = $this->cache->get('pages-tree', $succ);
        if ($succ)
           return $pages;
            
        // Read from database
        $dbpages = CM5_Model_Page::raw_query()
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

            if (!isset($p['children']))
                $p['children'] = array();

            if ($p['parent_id'] === null)
                $pages[] = & $p;
            else
            {
                $parent = & $indexed_pages[$p['parent_id']];
                if (!isset($parent['children']))
                    $parent['children'] = array( & $p);
                else
                    $parent['children'][] = & $p;
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
        foreach($root['children'] as $subpage)
        {
            $ret = $this->find_page_in_tree($page_id, $subpage);
            if ($ret !== null)
                return $ret;
        }
        return null;
            
    }
    
    /**
     * Get a sub tree from the whole tree based on the page_id.
     * @param integer $page_id
     * @return array With the subtree or null if not found.
     */
    public function getSubTree($page_id)
    {
        $tree = $this->getTree();
        foreach($tree as $page)
        {
            $ret = $this->find_page_in_tree($page_id, $page);
            if ($ret !== null)
                return $ret;
        }
        return null;
        
        
    }

    /**
     * Serve a url request to CMS. This is the actual entry point when a 
     * url is requested to be serverd.
     * @param string $url Force a custom url to server, or null if it is auto detected.
     */
    public function serve($url = null)
    {
    	$is_post = ($_SERVER['REQUEST_METHOD'] === 'POST');    	
        if ($url === null)
            $url = (isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'/');
        
        // Check cache first for response
        if (!$is_post) {
        	$response = $this->cache->get('url-' . $_SERVER['REQUEST_URI'], $succ);
        	if ($succ) {
            	$response->show();
            	exit;
        	}
        }
        
        // Initialize modules
        self::$instance->loadModules();

        // Initialize themes
        self::$instance->loadThemes();
        
        $response = new CM5_Response();
                
        // Dispatch page request to modules
        $stop_propagation = false;
        $this->events->filter('page.request', $stop_propagation, array('url' => $url, 'response' => $response));

        if (!$stop_propagation)
        {  
            // Check for CMS pages
            $selected_layout = $this->getSelectedTheme()->getLayoutClass();
        	$selected_layout::getInstance()->activateSlot();
            
            if ($url == '')
                $p = CM5_Model_Page::open(1);   // Home page
            else
            {
                $p = CM5_Model_Page::open_query()
                    ->where('uri = ?')
                    ->limit(1)
                    ->execute($url);

                if (!$p)
                    throw new Exception404();
                $p = $p[0];
            }
            $this->events->filter('page.post-fetchdata', $p, array('url' => $url, 'response' => $response));
            
            $this->events->filter('page.pre-render', $p, array('url' => $url, 'response' => $response));
            $selected_layout::getInstance()->getDocument()->title = $p->title . ' | ' . CM5_Config::getInstance()->site->title;
            etag('div class="article"',
                tag('h1 class="title"', $p->title),
                tag('div html_escape_off', $p->body)
            );
            $selected_layout::getInstance()->deactivate();
            $response->document = $selected_layout::getInstance()->getDocument()->render();

            // Trigger post render
            $this->events->filter('page.post-render', $response, array('url' => $url, 'page' => $p));
        }

        // Show response
        $response->show();
        
        // Add cache hook
        if (!$is_post && $response->cachable)
            $this->cache->set('url-' . $_SERVER['REQUEST_URI'], $response, 600);
    }
}
