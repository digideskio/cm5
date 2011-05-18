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

CM5_Layout_Admin::getInstance()->getDocument()->title = CM5_Config::getInstance()->site->title . " | Users panel";
Stupid::add_rule('user_myprofile',
    array('type' => 'url_path', 'chunk[3]' => '/^\+myprofile$/')
);
Stupid::add_rule('edit_user',
    array('type' => 'url_path', 'chunk[3]' => '/^([\w]+)$/', 'chunk[4]' => '/^\+edit$/')
);
Stupid::add_rule('delete_user',
    array('type' => 'url_path', 'chunk[3]' => '/^([\w]+)$/', 'chunk[4]' => '/^\+delete$/')
);
Stupid::add_rule('create_user',
    array('type' => 'url_path', 'chunk[3]' => '/^\+create$/')
);

Stupid::set_default_action('show_users');
Stupid::chain_reaction();


function user_myprofile()
{
    $user = CM5_Model_User::open(Authn_Realm::get_identity()->id());
    $frm = new CM5_Form_UserEditMyProfile($user);
    etag('div', $frm->render());
}

function edit_user($username)
{
    if (!($u = CM5_Model_User::open($username)))
        throw new Exception404();
        
    Layout::getActive()->getDocument()->title = CM5_Config::getInstance()->site->title . " | User: {$u->username}";

    $frm = new CM5_Form_UserEdit($u);
    etag('div', $frm->render());
}

function delete_user($username)
{
    if (!($u = CM5_Model_User::open($username)))
        throw new Exception404();
        
    if ($username == Authn_Realm::get_identity()->id())
    {
        etag('h2 class="error"', 'You cannot delete your self, login with another user before deleting this user.');
        etag('a', 'Back', array('href' => UrlFactory::craft('user.admin')));
        exit;
    }
    
    $frm = new CM5_Form_Confirm(
        "Delete user \"{$u->username}\"",
        "Are you sure? This action is inreversible!",
        'Delete',
        function($u){
            $u->delete();
            UrlFactory::craft("user.admin")->redirect();
        },
        array($u),
        UrlFactory::craft("user.admin")
    );
    etag('div', $frm->render());
}

function create_user()
{
    $frm = new CM5_Form_UserCreate();
    etag('div', $frm->render());
}

function show_users()
{
    $grid = new CM5_Widget_UsersGrid(CM5_Model_User::open_all());
    etag('div',
        $grid->render(),
        UrlFactory::craft('user.create')->anchor('Create user')
            ->add_class('button')
            ->add_class('add')
    );
}
