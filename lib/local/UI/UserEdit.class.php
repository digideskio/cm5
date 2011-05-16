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

class UI_UserEdit extends Form_Html
{
    public function __construct(CM5_Model_User $u)
    {
        $this->user = $u;

        parent::__construct(null, array('title' => 'Edit user "' . $u->username . '"',
            'attribs' => array('class' => 'form'),
		    'buttons' => array(
		        'save' => array('label' => 'Save'),
		        'cancel' => array('label' => 'Cancel', 'type' => 'button',
		            'attribs' => array('onclick' => "window.location='" . (string)UrlFactory::craft('user.admin') . "'"))
                )
            )
        );
        
        $this->addMany(
			field_password('password', array('label' => 'Reset password', 'pattern' => '/^.{3,}$/')),
			field_password('password2', array('label' => '', 'pattern' => '/^.{3,}$/',
				'hint' => 'Password must be at least 3 characters long.')),
			field_checkbox('enabled', array('label' => 'Enabled', 'checked' => $u->enabled, 'value' => true)),
			field_set('groups', array('label' => 'Groups'))
        );

        $enabledgroups = array();
        foreach($u->groups->all() as $g)
            $enabledgroups[] = $g->groupname;
        foreach(CM5_Model_Group::open_all() as $g) {
        	$checked = in_array($g->groupname, $enabledgroups);
        	$this->get('groups')->add(field_checkbox('group', array('label' => $g, 'value' => $g, 'checked' => $checked)));
        }
        
    }
    
    public function onProcessPost()
    {
        $pass1 = $this->get('password')->getValue();
        $pass2 = $this->get('password2')->getValue();
        
        if ((!empty($pass1)) || (!empty($pass2)))
            if ($pass1 != $pass2)
                $this->get('password2')->invalidate('Passwords do not match.');
    }

    public function onProcessValid()
    {
    	$values = $this->getValues();
    	
        $this->user->enabled = $values['enabled'];
        if (!empty($values['password']))
            $this->user->password = sha1($values['password']);
        $this->user->save();        

        
        foreach(CM5_Model_Group::open_all() as $g)
        	$groups[$g->groupname] = in_array($g->groupname, $values['groups']['group'])?true:false;        
        
        // Create memberships
        foreach($groups as $group => $enabled)
        {
            if ($enabled) {
            	if (count($this->user->groups->subquery()
            		->where('groupname = ?')->execute($group)) == 0) {
            			// Add membership
		            	CM5_Model_Membership::create(array(
		                    'username' => $this->user->username,
		                    'groupname' => $group
		                ));
            	}
            } else {
            	if (count($this->user->groups->subquery()
            		->where('groupname = ?')->execute($group))) {
            			// Remove membership
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
