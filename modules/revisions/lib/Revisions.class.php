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

class CM5_Module_Revisions extends CM5_Module
{
	private $revisioned_page = null;

	public function getConfigurableFields()
	{
		return array(
			'summary-mandatory' => 
				array('label' => 'Prohibit saving pages without a summary report.', 'type' => 'checkbox')
		);
	}
	
	public function getDefaultConfiguration()
	{
		return array(
			'summary-mandatory' => true
		);
	}
	
	/**
	 * Event handler for initialized@PageEdit
	 * @param Event $e
	 */
	public function pageEditInitialized(Event $e)
	{
		$form = $e->arguments['form'];

		// Change View to Preview
		$form->get('preview')->setValue(str_replace('>View<', '>Preview<', $form->get('preview')->getValue()));
		// Add Revisions
		$expanded_class = $this->getConfig()->{'summary-mandatory'}? ' expand':'';
		$form->add($set = field_set('revisions', array('label' => 'Versioning', 'attribs' => 
			array('class' => 'collapsable' . $expanded_class))));
		$set->add(field_text('summary',
			array('label' => '', 'attribs' => array('placeholder' => 'Write few words describing what have you changed.'),
				'required' => $this->getConfig()->{'summary-mandatory'})));
		$set->add(field_hidden('type', array('label' => '', 'value' => 'user')));
		
		// Create history
		$set->add($revs = field_raw('revisions', array('value' => tag('ul class="history"'))));
		$first = true;
		foreach($form->getPage()->revisions->subquery()->where('type <> ?')->order_by('created_at', 'DESC')->execute('preview') as $r) {
			$revs->getValue()->append($li = tag('li',
				tag('a', 'revert', array('data-url' => url($form->getPage()->getRelativeUrl()) . '?revision=' . $r->id))
					->add_class('button edit'),
				tag('a target="_blank"', 'view', array('href' => url($form->getPage()->getRelativeUrl()) . '?revision=' . $r->id))
					->add_class('button navigate'),	
				tag('span class="date"', date_exformat($r->created_at)->human_diff())
			));

			if ($r->type == 'auto')
				$li->add_class('auto')
					->append(tag('span class="auto"', 'auto'));
					
			$li->append(
				tag('span class="summary"', $r->summary),
				tag('span class="author"', $r->author),
				tag('span class="ip"', $r->ip)
			)->add_class($first?'current':'');
			
			
			$first = false;
		}
	}

	/**
	 * Event handler for process.post@PageEdit
	 * @param Event $e
	 */
	public function pageEditProcessPost(Event $e)
	{
		$form = $e->arguments['form'];
		$values = $form->getValues();
		if ($summary = $form->get('revisions')->get('summary')->getValue()) {
			CM5_Module_RevisionModel::setNextSummary($summary);
		}
		
		// Normalize values
		$values['title'] = isset($values['title'])?$values['title']:null;
		$values['slug'] = isset($values['slug'])?$values['slug']:null;
		if ($values['revisions']['type'] == 'preview') {
			$r = CM5_Module_RevisionModel::createPreview(
				$form->getPage(), $values['title'], $values['slug'], $values['body']);
			Net_HTTP_Response::redirect(url($form->getPage()->getRelativeUrl() . '?revision=' . $r->id));
		}
	}

	//! Initilaize for backend
	public function initializeBackend()
	{
		// Add needed dependancies for the backend
		CM5_Form_PageEdit::events()->connect('initialized', array($this, 'pageEditInitialized'));
		CM5_Form_PageEdit::events()->connect('process.post', array($this, 'pageEditProcessPost'));
		CM5_Layout_Admin::getInstance()->getDocument()->add_ref_css(surl('/modules/revisions/static/revisions.css'));
		CM5_Layout_Admin::getInstance()->getDocument()->add_ref_js(surl('/modules/revisions/static/revisions.js'));
	}
	
	//! Initialize for frontend
	public function initializeFrontend()
	{
		CM5_Core::getInstance()->events()->connect('page.post-fetchdata', array($this, 'onPagePostFetchData'));
	}

	//! Initialize module
	public function onInitialize()
	{
		// Adding model add hooks also
		require __DIR__ . '/RevisionModel.class.php';
		 
		if (CM5_Core::getInstance()->getWorkingContext() == 'backend')
			$this->initializeBackend();
		else
			$this->initializeFrontend();
	}

	public function onPagePostFetchData(Event $event)
	{
		if (!isset($_GET['revision']))
			return;

		// Skip caching
		$page = $event->filtered_value;
		$event->arguments['response']->cachable = false;

		require_once __DIR__ . '/../../../web/authnz.php';
		if (!Authn_Realm::has_identity())
			throw new Exception404();
			
		if ((!($rev = CM5_Module_RevisionModel::open($_GET['revision']))) ||
			($rev->page_id != $page->id))
				throw new Exception404(); // Page_id - rev_id do not match
		 
		// Load this revision to page object
		$rev->storeCopyToPage($page);
		$this->revisioned_page = $page;
		
		// If it is a an xml request return json
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			header('Content-Type: application/json');
			Layout::getActive()->deactivate();
			echo json_encode($page->to_array());
			exit;
		}
	}

	public function onEnable()
	{
		$dbprefix = CM5_Config::getInstance()->db->prefix;
		if (DB_Conn::get_link()->multi_query(require(__DIR__ . '/../install/build-script.php')))
		while (DB_Conn::get_link()->next_result());
	}
}
