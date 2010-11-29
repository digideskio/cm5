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

class UI_UserEditMyProfile extends Output_HTML_Form
{
    public function __construct($u)
    {
        $this->user = $u;
        
        $groups = array();
        foreach(Group::open_all() as $g)
            $groups[$g->groupname] = $g->groupname;
        
        $groupselected = array();
        foreach($u->groups->all() as $g)
            $groupselected[$g->groupname] = true;

        parent::__construct(array(
            'old-password' => array('display' => 'Current password', 'type' => 'password'),
			'password' => array('display' => 'New password', 'type' => 'password',
			    'onerror' => 'Password must be at least 3 characters long.'),
			'password2' => array('display' => ' ', 'type' => 'password')
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
        $currentpass = $this->get_field_value('old-password');
        if (!Authn_Realm::get_backend()->authenticate($this->user->username, $currentpass))
        {
            $this->invalidate_field('old-password', 'You gave wrong password');
        }
        $pass1 = $this->get_field_value('password');
        $pass2 = $this->get_field_value('password2');
        
        if ((empty($pass1)) || (empty($pass2)) ||
            ($pass1 != $pass2))
                $this->invalidate_field('password2', 'You must write two times the same NEW password.');
    }

    public function on_valid($values)
    {
        if (!empty($values['password']))
            $this->user->password = sha1($values['password']);
        $this->user->save();
        UrlFactory::craft('user.admin')->redirect();
    }
};

?>
