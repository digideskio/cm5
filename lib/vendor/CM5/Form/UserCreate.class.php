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
 * Form to create a new user
 */
class CM5_Form_UserCreate extends Form_Html
{
    public function __construct()
    {
        $groups = array();
        

        parent::__construct(null, array('title' => 'Create new user',
            'attribs' => array('class' => 'form'),
		    'buttons' => array(
		        'create' => array('label' => 'Create'),
		        'cancel' => array('label' => 'Cancel', 'type' => 'button',
		            'onclick' => "window.location='" . (string)UrlFactory::craft('user.admin') . "'")
                )
            )
        );
        
        $this->addMany(
			field_text('username', array('label' => 'Username', 'pattern' => '/^[a-z0-9_\-]+$/',
				'required' => true, 'hint' => 'Permitted letters are lower case letters, numbers, dash and underscore.')),
			field_password('password', array('label' => 'Password', 'pattern' => '/^.{3,}$/',
				'required' => true)),
			field_password('password2', array('label' => '', 'required' => true, 'hint' => 'At least 3 letters long.')),
			field_set('groups', array('label' => 'Groups'))
		);
		foreach(CM5_Model_Group::open_all() as $g)
			$this->get('groups')->add(field_checkbox('group', array('label' => $g, 'value' => $g)));
        
    }
    
    public function onProcessPost()
    {
    	if ($this->get('username')->isValid()) {
    		if (CM5_Model_User::open($this->get('username')->getValue()))
    			$this->get('username')->invalidate('There is already a user with that username.');
    	}
        if ($this->get('password')->getValue() != $this->get('password2')->getValue())
            $this->get('password2')->invalidate('Passwords do not match.');
    }

    public function onProcessValid()
    {
    	$values = $this->getValues();
    	
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
		foreach($values['groups']['group'] as $group) {
			CM5_Model_Membership::create(array(
				'username' => $values['username'],
				'groupname' => $group
			));
		}
        
        UrlFactory::craft('user.admin')->redirect();
    }
};
