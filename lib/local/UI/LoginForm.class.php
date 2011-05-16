<?php
/*
 *  This file is part of PHPLibs <http://phplibs.kmfa.net/>.
 *  
 *  Copyright (c) 2010 < squarious at gmail dot com > .
 *  
 *  PHPLibs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  PHPLibs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with PHPLibs.  If not, see <http://www.gnu.org/licenses/>.
 *  
 */


class UI_LoginForm extends Form_Html
{
    public function __construct($redirect_url)
    {
        $this->redirect_url = $redirect_url;

        parent::__construct(null, array(
        	'title' => GConfig::get_instance()->site->title . ' Login',
            'attribs' => array('class' => 'form login'),
		    'buttons' => array(
		        'login' => array('label' =>'Login'),
		        'back' => array('label' => 'Back', 'type' => 'button',
		        	'attribs' => array('onclick' => "window.location='{$redirect_url}'"))
                )
            )
        );
        
        $this->addMany(
        	field_text('user', array('label' => 'Username', 'required' => true, 'autofocus' => true)),
        	field_text('pass', array('label' => 'Password', 'type' => 'password', 'required' => true))
        );
    }

    public function onProcessValid()
    {
        $user = $this->get('user')->getValue();
        $pass = $this->get('pass')->getValue();
        if (Authn_Realm::authenticate($user, $pass))
        {
            CM5_Logger::get_instance()->info("User \"{$user}\" logged on.");
            Net_HTTP_Response::redirect($this->redirect_url);
        }
        else
        {
            CM5_Logger::get_instance()->err("User \"{$user}\" tried to login unsuccesfully.");
            $this->get('pass')->invalidate('The username or password you entered is incorrect.');
        }
    }
};
