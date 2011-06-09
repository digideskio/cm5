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

class CM5_Module_ContentMagic extends CM5_Module
{
	protected $events;

	//! Initialize module
	public function onInitialize()
	{
		$this->events = new EventDispatcher(array(
			'subpages.render-line'
		));
		$c = CM5_Core::getInstance();
		$c->events()->connect('page.pre-render', array($this, 'onPagePreRender'));
		$c->events()->connect('page.post-render', array($this, 'onPagePostRender'));
	}

	public function events()
	{
		return $this->events;
	}
	
	private function replaceSubpages(CM5_Model_Page $p)
	{
		if (strstr($p->body, '##subpages##') === false)
		return;

		// Create contents index
		$subpages = CM5_Model_Page::open_query()
			->where('status = ?')
			->where('parent_id = ?')
			->order_by('order', 'ASC')
			->execute('published', $p->id);

		$contents_el = tag('div class="subpages-index"', $ul = tag('ul'));
		foreach($subpages as $sp) {
			$li = tag('li', UrlFactory::craft('page.view', $sp)->anchor($sp->title));
			if ($this->events)
				$this->events->filter('subpages.render-line', $li, array('page' => $sp));
			$ul->append($li);
		}

		$p->body = str_replace('##subpages##', (string)$contents_el, $p->body);
	}

	private function executeRedirect(CM5_Model_Page $p, CM5_Response $r)
	{
		if (strstr($p->body, '##redirect ') === false)
			return;

		if (!preg_match('/##redirect\s+(?P<url>(http)?|.+)\s*##/m', $r->document, $matches))
			return;

		if (empty($matches['url']))
			return;

		$r->addHeader('Location: ' . $matches['url']);

	}

	//! Handler for pre rendering
	public function onPagePreRender($event)
	{
		$p = $event->filtered_value;

		// Execute subpages
		$this->replaceSubpages($p);
	}

	public function onPagePostRender($event)
	{
		$resp = $event->filtered_value;

		// Execute subpages
		$this->executeRedirect($event->arguments['page'] , $resp);
	}
}

return array(
	'class' => 'CM5_Module_ContentMagic',
	'nickname' => 'content-magic',
	'title' => 'Content Magic',
	'description' => 'Provides a set of magic keywords to add subpages indexing, redirection etc.'
);
