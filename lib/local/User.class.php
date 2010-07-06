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


class User extends DB_Record
{
    static public function get_table()
    {   
        return Config::get('db.prefix') . 'users';
    }

    static public $fields = array(
        'username' => array('pk' => true),
        'password',
        'enabled'
        );
}

User::events()->connect('op.pre.delete', create_function('$e',
'
    Membership::raw_query("Membership")
        ->delete()
        ->where("username = ?")
        ->execute($e->arguments["record"]->username);
'));

User::one_to_many('Page', 'user', 'articles');
Group::many_to_many('User', 'Membership', 'groups', 'users');
?>
