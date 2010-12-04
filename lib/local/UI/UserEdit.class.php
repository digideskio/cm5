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

class UI_UserEdit extends Output_HTML_Form
{
    public function __construct(CM5_Model_User $u)
    {
        $this->user = $u;
        
        $groups = array();
        foreach(CM5_Model_Group::open_all() as $g)
            $groups[$g->groupname] = $g->groupname;
        
        $groupselected = array();
        foreach($u->groups->all() as $g)
            $groupselected[$g->groupname] = true;

        parent::__construct(array(
			'password' => array('display' => 'Reset password', 'type' => 'password',
			    'onerror' => 'Password must be at least 3 characters long.'),
			'password2' => array('display' => ' ', 'type' => 'password'),
			'enabled' => array('display' => 'Enabled', 'type' => 'checkbox', 'value' => $u->enabled),
			'groups' => array('display' => 'Groups', 'value' => $groupselected,
			    'type' => 'checklist', 'optionlist' => $groups)
        ),
        array('title' => 'Edit user "' . $u->username . '"',
            'css' => array('ui-form'),
		    'buttons' => array(
		        'save' => array('display' => 'Save'),
		        'cancel' => array('display' => 'Cancel', 'type' => 'button',
		            'onclick' => "window.location='" . (string)UrlFactory::craft('user.admin') . "'")
                )
            )
        );
    }
    
    public function on_post()
    {
        $pass1 = $this->get_field_value('password');
        $pass2 = $this->get_field_value('password2');
        
        if ((!empty($pass1)) || (!empty($pass2)))
            if ($pass1 != $pass2)
                $this->invalidate_field('password2', 'Passwords do not match.');
    }

    public function on_valid($values)
    {
        $this->user->enabled = $values['enabled'];
        if (!empty($values['password']))
            $this->user->password = sha1($values['password']);
        $this->user->save();        

        $groups = array();
        foreach(CM5_Model_Group::open_all() as $g)
        	$groups[$g->groupname] = false;
        $groups = array_merge($groups, $values['groups']);
        
        // Create memberships
        foreach($groups as $group => $enabled)
        {
            if ($enabled) {
            	if (count($this->user->groups->subquery()
            		->where('groupname = ?')->execute($group)) == 0) {
		            	CM5_Model_Membership::create(array(
		                    'username' => $this->user->username,
		                    'groupname' => $group
		                ));
            	}
            } else {
            	if (count($this->user->groups->subquery()
            		->where('groupname = ?')->execute($group))) {
            			CM5_Model_Membership::open(array(
            				'username' => $this->user->username,
                    		'groupname' => $group)
            			)->delete();            			
            	}
            }
        }
        
        UrlFactory::craft('user.admin')->redirect();
    }
};

?>
