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
    public function enhanceEditForm(Event $e)
    {
    	$form = $e->arguments['form'];
    	
    	$form->add($set = field_set('revisions', array('label' => 'History')));
    	$set->add($revs = field_raw('revisions', array('value' => tag('ul class="revisions"'))));

    	foreach($form->getPage()->revisions->subquery()->order_by('created_at', 'DESC')->execute() as $r) {
    		$revs->getValue()->append(tag('li',
    			tag('span class="author"', $r->author),    			
    			tag('span class="summary"', $r->summary),
    			tag('span class="date"', date_exformat($r->created_at)->human_diff()),
    			tag('span class="ip"', $r->ip),
    			tag('a class="navigate" target="_blank"', 'view', array('href' => url($form->getPage()->getRelativeUrl()) . '?revision=' . $r->id))
    		));
    	}
    	
    	$form->options['buttons']['preview'] = array('label' => 'preview', 'type' => 'submit');
    }
    
    //! Initialize module
    public function onInitialize()
    {
    	// Adding model add hooks also
    	require __DIR__ . '/RevisionModel.class.php';    	
    	
    	if (CM5_Core::getInstance()->getWorkingContext() == 'backend') {
	    	// Add needed dependancies for the backend
	    	
    		CM5_Form_PageEdit::events()->connect('initialized', array($this, 'enhanceEditForm'));
	    	CM5_Layout_Admin::getInstance()->getDocument()->add_ref_css(surl('/modules/revisions/static/extra-admin.css'));
    	} else {
    		CM5_Core::getInstance()->events()->connect('page.pre-render', array($this, 'onPagePreRender'));
    	}
    }
    
    public function onPagePreRender(Event $event)
    {
    	$page = $event->filtered_value;
    	
    	// Skip caching
    	if (isset($_GET['revision']))
    		$event->arguments['response']->cachable = false;
    		
		// TODO: add security check here for authenticated users only.
    	if ((!($rev = CM5_Module_RevisionModel::open($_GET['revision']))) ||
    		($rev->page_id != $page->id))
    		return;

    	error_log("Loaded revision " . $rev->id);
    	
    	// Load this revision to page object    	
    	$rev->storeCopyToPage($page);
    }
    
    public function onEnable()
    {
		$dbprefix = CM5_Config::getInstance()->db->prefix;
		if (DB_Conn::get_link()->multi_query(require(__DIR__ . '/../install/build-script.php')))
			while (DB_Conn::get_link()->next_result());    	
    }
}
