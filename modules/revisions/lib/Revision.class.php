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


class CM5_Module_Revision extends DB_Record
{
    static public function get_table()
    {   
        return GConfig::get_instance()->db->prefix . 'mod_revisions_revs';
    }
	
	public static $fields = array(
		'id' => array('ai' => true, 'pk' => true),
		'page_id' => array('fk' => 'CM5_Model_Page'),
		'new_title',
		'old_title',
		'new_slug',
		'old_slug',
		'new_body',
		'old_body',
		'author' => array('fk' => 'CM5_Model_User'),
		'created_at' => array('type' => 'datetime')
	);
}

CM5_Model_Page::one_to_many('CM5_Module_Revision', 'page', 'revisions');
CM5_Model_User::one_to_many('CM5_Module_Revision', 'user', 'revisions');

CM5_Model_Page::events()->connect('op.pre.save', create_function('$e', '
	$p = $e->arguments["record"];
	$old_values = $e->arguments["old_values"];
		
	$rev = array();		
	if (isset($old_values["body"]) && ($p->body != $old_values["body"])) {
			$rev["new_body"] = $p->body;
	}
	if (isset($old_values["title"]) && ($p->title != $old_values["title"])) {
		$rev["new_title"] = $p->title;
		$rev["old_title"] = $old_values["title"];
	}
	if (isset($old_values["slug"]) && ($p->slug != $old_values["slug"])) {
		$rev["new_slug"] = $p->slug;
		$rev["old_slug"] = $old_values["slug"];
	}
	
	if (count($rev) > 0) {
		$rev["page_id"] = $p->id;
		$rev["created_at"] = new DateTime();
		$rev["author"] = Authn_Realm::get_identity()->id();
		CM5_Module_Revision::create($rev);
	}
'));