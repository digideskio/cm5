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
 * Search form
 */
class CM5_Module_Search_Form extends Form_Html
{
	private $button_label;
	
	public function __construct($button_label)
	{
		$this->button_label = ($button_label == null)?'Search':$button_label;
		parent::__construct(null, array(
			'title' => '',
			'method' => 'get',
			'attribs' => array(
				'class' => 'form search'),
			'buttons' => array(
				'search' => array('label' => $this->button_label)
			)
		));
	}
	
	public function onInitialized()
	{
		$this->add(field_search('query', array('label' => '', 'required' => true)));
	}
}


/**
 * Nice and clever paginator renderer
 * @param Paginator $paginator
 */
function render_paginator(Paginator $paginator, $craft_href, $labels = null, $walk_distance = 2)
{
	// Normalize labels
	if (!is_array($labels))
		$labels = array();
	$labels = array_merge(array(
		'first' => 'First',
		'previous' => 'Previous',
		'next' => 'Next',
		'last' => 'Last',
		'gap' => '..'), $labels);
	
	// Construct div
	$div = tag('div')->add_class('paginator');
	
	
	$cpage = $paginator->getCurrentPage();
	if ($cpage->getIndex() - $walk_distance > 1) {
		$div->append(tag('a class="first"', $labels['first'])->attr('href', $craft_href($paginator->getFirstPage())));			
	}
	if (!$cpage->isFirstPage()) {
		$div->append(tag('a class="previous"', $labels['previous'])->attr('href', $craft_href($cpage->getPrevious())));			
	}
	
	// Walk distance previous
	$previous = $cpage->getIndex() - $walk_distance;
	if ($previous <= 1)
		$previous = 1;
	else
		tag('span class="gap"', $labels['gap'])->appendTo($div);
	for($i = $previous; $i < $cpage->getIndex(); $i++)
		$div->append(tag('a', (string)$paginator[$i])->attr('href', $craft_href($paginator[$i])));

	// Current
	$div->append(tag('span class="current"', (string)$cpage)->attr('href', $craft_href($cpage)));
	
	// Walk distance next
	$next = $cpage->getIndex() + $walk_distance;
	if ($next >= count($paginator))
		$next = count($paginator);	
	for($i = $cpage->getIndex() + 1; $i <= $next ; $i++)
		$div->append(tag('a', (string)$paginator[$i])->attr('href', $craft_href($paginator[$i])));		
	
	if ($cpage->getIndex() + $walk_distance < count($paginator))	
		tag('span class="gap"', $labels['gap'])->appendTo($div);
	if (!$cpage->isLastPage()) {
		$div->append(tag('a class="next"', $labels['next'])->attr('href', $craft_href($cpage->getNext())));			
	}
	if ($cpage->getIndex() + $walk_distance < count($paginator)) {
		$div->append(tag('a class="last"', $labels['last'])->attr('href', $craft_href($paginator->getLastPage())));			
	}
	
	return $div;
} 

/**
 * Index and Search module.
 */
class CM5_Module_Search extends CM5_Module
{	
	private $events;
	
	/**
	 * Available events are :
	 *  - results.post-process
	 *  - results.post-render
	 */
	public function events()
	{
		return $this->events;
	}
	
	public function onInitialize()
	{
		require_once __DIR__ . '/lib/Indexer.class.php';
		//CM5_Core::getInstance()->events()->connect('page.request', array($this, 'onEventPageRequest'));
		CM5_Core::getInstance()->events()->connect('page.pre-render', array($this, 'onPagePreRender'));
		
		$this->events = new EventDispatcher(array(
			'results.post-process',
			'results.post-render',
		));
	}
	
	/**
	 * On enable create search index
	 * (non-PHPdoc)
	 * @see CM5_Module::onEnable()
	 */
	public function onEnable()
	{
		if (!is_writable(__DIR__ . '/_data')) {
			CM5_Logger::getInstance()->err('"search/_data" folder is not writable.');
			return false;
		}
		
		require_once __DIR__ . '/lib/Indexer.class.php';
		CM5_Module_Search_Indexer::rebuild();		
	}
	
	/**
	 * Get hit results for a query
	 */
	public function getSearchResults($query_text)
	{
		$query = Zend_Search_Lucene_Search_QueryParser::parse($query_text, 'UTF-8');
		$results = CM5_Module_Search_Indexer::open()->getEngine()->find($query);
	}
	
	/**
	 * Generate search interface
	 */
	public function generateSearchUi($button_label)
	{
		$div = tag('div class="search"')->push_parent();
		
		$frm = new CM5_Module_Search_Form($button_label);
		$frm->process($_GET);
		Output_HTMLTag::get_current_parent()->append($frm->render());
		if ($frm->getResultCode() == Form::RESULT_VALID) {
			$query_str = $frm->get('query')->getValue();
			$query = Zend_Search_Lucene_Search_QueryParser::parse($query_str, 'UTF-8');
			$results = new Paginator(CM5_Module_Search_Indexer::open()->getEngine()->find($query), 5);
			$results->setCurrentIndex(isset($_GET['page'])?$_GET['page']:1);
			
			etag('em', 'found ' . count($results->getData()) . ' pages.');
			$ul = etag('ul')->add_class('search results');
			if ($results->isPaginated()) {
				$d = render_paginator($results, function(Paginator_Page $page){
					return $_SERVER['REQUEST_URI'] . (count($_GET)?'&':'?') . 'page=' . $page; 
				});
				Output_HTMLTag::get_current_parent()->append($d);
			}
			if (count($results)) {
				foreach($results->getCurrentPage() as $hit) {
					$li = tag('li',
						tag('a class="title"', $hit->getDocument()->title)->attr('href', url($hit->getDocument()->url)),
						tag('p html_escape_off', mb_strcut($hit->getDocument()->body, 0, 500, 'UTF-8'))
					);
					$this->events->filter('results.post-render', $li, array('query' => $query_str, 'hit' => $hit));
					$ul->append($li);
				}
			}
		}
				
		
		Output_HTMLTag::pop_parent();
		return $div;
	}
	
	public function onEventPageRequest(Event $event) {
		$response = $event->arguments['response'];
		if ($event->arguments['url'] == '/search') {
			$event->filtered_value = true;
			$response->document = $this->generateSearchUi()->render();
			$response->cachable= false;
		}
	}
	
	/**
	 * Replace search magic keywords
	 * @param CM5_Model_Page $page
	 */
	public function replaceSearch(CM5_Model_Page $page)
	{
		if (strstr($page->body, '##search') === false)
			return;

		if (!preg_match('/##search[^#]*##/s', $page->body, $matches, PREG_OFFSET_CAPTURE))
			return;
		$replace = array('start' => $matches[0][1], 'length' => strlen($matches[0][0]));
		
		$search_text = html_entity_decode(preg_replace('/\<[\s]*br[^\>]*\>/s', '', $matches[0][0]));
		$search_text = preg_replace('/\n[\s]+/s', "\n", $search_text);
		
		
		if (!preg_match('/##search([\s]+\"(?P<button_label>[^\"]+)\")?[^#]*##/s', $search_text, $matches))
			return;

		$button_label = isset($matches['button_label'])?$matches['button_label']:null;
		$search = (string)$this->generateSearchUi($button_label);
		$page->body = substr_replace($page->body, (string)$search, $replace['start'], $replace['length']);
	}
	
	/**
	 * Handler for pre rendering
	 */ 
	public function onPagePreRender($event)
	{
		$p = $event->filtered_value;
		//$event->arguments['response']->cachable = false;

		// Execute subpages
		$this->replaceSearch($p);
	}
}

return array(
	'class' => 'CM5_Module_Search',
	'nickname' => 'search',
	'title' => 'Indexing and Search',
	'description' => 'Add support for searching on site content.',
);
