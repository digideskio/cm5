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

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/vendor');
require_once __DIR__ . '/vendor/Zend/Search/Lucene.php';

class CM5_Module_Search_Indexer
{
	//! Instance of zend search engine
	protected $engine = null;
	
	/**
	 * Directory where indexing is saved.
	 * @var string
	 */
	public static $index_directory;
	
	/**
	 * Create a new indexer
	 * @param Zend_Search_Lucene $engine
	 */
	public function __construct($engine)
	{
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(
        	new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8());
		$this->engine = $engine;
	}
	
	/**
	 * Get the current engine instance
	 * @return Zend_Search_Lucene
	 */
	public function getEngine()
	{
		return $this->engine;
	}
	
	/**
	 * Add a page on indexing engine.
	 * @param CM5_Model_Page $page
	 */
	public function addPage(CM5_Model_Page $page)
	{
		$spage = new Zend_Search_Lucene_Document();
 
		$spage->addField(Zend_Search_Lucene_Field::keyword('id', $page->id));
		$spage->addField(Zend_Search_Lucene_Field::Text('title', $page->title, 'UTF-8'));
		$spage->addField(Zend_Search_Lucene_Field::Text('slug', $page->slug, 'UTF-8'));
		$spage->addField(Zend_Search_Lucene_Field::Text('url', $page->getRelativeUrl(), 'UTF-8'));
		$spage->addField(Zend_Search_Lucene_Field::Text('body', strip_html_tags($page->body), 'UTF-8'));
		 
		// Add document to the index
		$this->engine->addDocument($spage);
	}
	
	/**
	 * Remove a page from indexing
	 * @param CM5_Model_Page $page
	 */
	public function removePage(CM5_Model_Page $page)
	{
		$hits = $this->engine->find('id:' . $page->id);
		foreach($hits as $hit)
			$this->engine->delete($hit->id);
	}
	
	/**
	 * Update a page with new database content
	 * @param CM5_Model_Page $page
	 */
	public function updatePage(CM5_Model_Page $page)
	{
		$this->removePage($page);
		$this->addPage($page);
	}
	
	/**
	 * Open an existing index or create it.
	 * @return CM5_Module_Search_Indexer
	 */
	static public function open()
	{
		try{
			return new static(Zend_Search_Lucene::open(self::$index_directory));
		} catch(Zend_Search_Lucene_Exception $e) {
			return static::create(self::$index_directory);
		}
	}
	
	/**
	 * Recreate index by erasing all entries and rebuild it.
	 * @return CM5_Module_Search_Indexer
	 */
	public static function rebuild()
	{
		$d = dir(self::$index_directory);
		while (false !== ($entry = $d->read())) {
			$full_path = self::$index_directory . '/' . $entry;
			if (is_dir($full_path ))
				continue;
			
	   		unlink($full_path);
		}
		$d->close();
		return self::create();
	}
	
	/**
	 * Try to create index from the scratch
	 * @return CM5_Module_Search_Indexer
	 */	
	public static function create()
	{
		$engine = Zend_Search_Lucene::create(self::$index_directory);
		$search = new self($engine);
		foreach(CM5_Model_Page::open_all() as $p)
			$search->addPage($p);
		$engine->optimize();	
		return $search;
	}
}


// Enable UTF-8 analyzer
Zend_Search_Lucene_Analysis_Analyzer::setDefault(
    new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
    
CM5_Module_Search_Indexer::$index_directory = __DIR__ . '/../_data';


// Bind to page events
CM5_Model_Page::events()->connect('op.pre.delete', function(Event $e) {
	CM5_Module_Search_Indexer::open()->removePage($e->arguments['record']);
});

CM5_Model_Page::events()->connect('op.post.save', function(Event $e) {
	CM5_Module_Search_Indexer::open()->updatePage($e->arguments['record']);
});

CM5_Model_Page::events()->connect('op.post.create', function(Event $e) {
	CM5_Module_Search_Indexer::open()->addPage($e->arguments['record']);
});