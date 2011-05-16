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
 * Form to edit a user by his prespective.
 */
class CM5_Form_UserEditMyProfile extends Form_Html
{
	/**
	 * @param CM5_Model_User $u User to be edited.
	 */
    public function __construct(CM5_Model_User $u)
    {
        $this->user = $u;

        parent::__construct(null, array('title' => 'Change your profile',
            'attribs' => array('class' => 'form'),
		    'buttons' => array(
		        'save' => array('label' => 'Save'),
		        'cancel' => array('label' => 'Cancel', 'type' => 'button',
		            'attribs' => array('onclick' => "window.location='" . (string)UrlFactory::craft('user.admin') . "'"))
                )
            )
        );
        $this->addMany(
            field_password('old-password', array('label' => 'Current password')),
			field_password('password', array('label' => 'New password', 'pattern' => '/^.{3,}$/')),
			field_password('password2', array('label' => '', 'hint' => 'Password must be at least 3 characters long.'))
        );
    }
    
    public function onProcessPost()
    {
        $currentpass = $this->get('old-password')->getValue();
        if (!Authn_Realm::get_backend()->authenticate($this->user->username, $currentpass))
        {
            $this->get('old-password')->invalidate('You gave wrong password');
        }
        $pass1 = $this->get('password')->getValue();
        $pass2 = $this->get('password2')->getValue();
        
        if ((empty($pass1)) || (empty($pass2)) ||
            ($pass1 != $pass2))
                $this->get('password2')->invalidate('You must write two times the same NEW password.');
    }

    public function onProcessValid()
    {
    	$values = $this->getValues();
        if (!empty($values['password']))
            $this->user->password = sha1($values['password']);
        $this->user->save();
        UrlFactory::craft('user.admin')->redirect();
    }
};
