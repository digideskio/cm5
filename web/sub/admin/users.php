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

Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . " | Users panel";
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
        
    Layout::open('admin')->activate();

    $frm = new UI_UserEditMyProfile($user);
    etag('div', $frm->render());
}

function edit_user($username)
{
    if (!($u = CM5_Model_User::open($username)))
        not_found();
        
    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . " | User: {$u->username}";
    Layout::open('admin')->activate();

    $frm = new UI_UserEdit($u);
    etag('div', $frm->render());
}

function delete_user($username)
{
    if (!($u = CM5_Model_User::open($username)))
        not_found();
        
    Layout::open('admin')->activate();
    if ($username == Authn_Realm::get_identity()->id())
    {
        etag('h2 class="error"', 'You cannot delete your self, login with another user before deleting this user.');
        etag('a', 'Back', array('href' => UrlFactory::craft('user.admin')));
        exit;
    }
    
    $frm = new UI_ConfirmForm(
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
    Layout::open('admin')->activate();
    
    $frm = new UI_UserCreate();
    etag('div', $frm->render());
}

function show_users()
{
    Layout::open('admin')->activate();

    $grid = new UI_UsersGrid(CM5_Model_User::open_all());
    etag('div',
        $grid->render(),
        UrlFactory::craft('user.create')->anchor('Create user')
            ->add_class('button')
            ->add_class('add')
    );
}
