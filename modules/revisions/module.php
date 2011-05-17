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
    //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'revisions',
            'title' => 'Revisions Tracking',
            'description' => 'Add support for creating pages revisions.'
        );
    }
    
    public function enhance_edit_form(Event $e)
    {
    	$form = $e->arguments['form'];
    	
    	$form->add($set = field_set('revisions', array('label' => 'History')));
    	$set->add($revs = field_raw('revisions', array('value' => tag('ul class="revisions"'))));

    	foreach($form->getPage()->revisions->subquery()->order_by('created_at', 'DESC')->execute() as $r) {
    		$revs->getValue()->append(tag('li',
    			tag('span class="author"', $r->author),    			
    			tag('span class="summary"', $r->summary),
    			tag('span class="date"', date_exformat($r->created_at)->human_diff()),
    			tag('span class="ip"', $r->ip)
    		));
    	}
    }
    
    //! Initialize module
    public function init()
    {
    	// Adding model add hooks also
    	require __DIR__ . '/lib/Revision.class.php';    	
    	CM5_Form_PageEdit::events()->connect('initialized', array($this, 'enhance_edit_form'));
    }
    
    public function on_enable()
    {
		$dbprefix = CM5_Config::get_instance()->db->prefix;
		if (DB_Conn::get_link()->multi_query(require(__DIR__ . '/install/build-script.php')))
			while (DB_Conn::get_link()->next_result());    	
    }
}

CM5_Module_Revisions::register();
