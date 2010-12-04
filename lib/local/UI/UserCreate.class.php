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

class UI_UserCreate extends Output_HTML_Form
{
    public function __construct()
    {
        $groups = array();
        foreach(CM5_Model_Group::open_all() as $g)
            $groups[$g->groupname] = $g->groupname;

        parent::__construct(array(
			'username' => array('display' => 'Username', 'regcheck' => '/^[a-z0-9_\-]+$/',
			    'onerror' => 'Username can have lower case letters, numbers, dash and underscore.'),
			'password' => array('display' => 'Password', 'regcheck' => '/^.{3,}$/', 'type' => 'password',
			    'onerror' => 'Password must be at least 3 characters long.'),
			'password2' => array('display' => ' ', 'type' => 'password'),
			'groups' => array('display' => 'Groups', 
			    'type' => 'checklist', 'optionlist' => $groups)
        ),
        array('title' => 'Create new user',
            'css' => array('ui-form'),
		    'buttons' => array(
		        'create' => array('display' => 'Create'),
		        'cancel' => array('display' => 'Cancel', 'type' => 'button',
		            'onclick' => "window.location='" . (string)UrlFactory::craft('user.admin') . "'")
                )
            )
        );
    }
    
    public function on_post()
    {
        if ($this->get_field_value('password') != $this->get_field_value('password2'))
            $this->invalidate_field('password2', 'Passwords do not match.');
    }

    public function on_valid($values)
    {

        $u = CM5_Model_User::create(array(
            'username' => $values['username'],
            'password' => sha1($values['password']),
            'enabled' => true
        ));
        
        if (!$u)
        {
            $this->invalidate_field('username', 'There was an error creating user.');
            return;
        }

        // Create memberships
        foreach($values['groups'] as $group => $enabled)
        {
            if ($enabled)
                CM5_Model_Membership::create(array(
                    'username' => $values['username'],
                    'groupname' => $group
                ));
        }
        
        UrlFactory::craft('user.admin')->redirect();
    }
};

?>
