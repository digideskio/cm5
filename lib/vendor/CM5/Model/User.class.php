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


/**
 * Model for users table. 
 * @author sque@0x0lab.org
 *
 * @property string $username
 * @property string $password
 * @property boolean $enabled
 * 
 * @property array $groups
 * @property array $articles
 */
class CM5_Model_User extends DB_Record
{
    static public function get_table()
    {   
        return CM5_Config::getInstance()->db->prefix . 'users';
    }

    static public $fields = array(
        'username' => array('pk' => true),
        'password',
        'enabled'
        );
}

CM5_Model_User::events()->connect('op.post.create', function($e) {
    $u = $e->arguments["record"];

    // Log event
    CM5_Logger::getInstance()->notice("User \"{$u->username}\" was created.");
});

CM5_Model_User::events()->connect('op.pre.delete', function($e) {
    $u = $e->arguments["record"];
    CM5_Model_Membership::raw_query("CM5_Model_Membership")
        ->delete()
        ->where("username = ?")
        ->execute($u->username);
        
    CM5_Logger::getInstance()->notice("User \"{$u->username}\" was deleted.");
});

CM5_Model_User::events()->connect('op.pre.save', function($e) {
    // Update last modified
    $u = $e->arguments["record"];
   
    // Log event
    if (in_array("password", array_keys($e->arguments["old_values"])))
    	CM5_Logger::getInstance()->notice("User \"{$u->username}\" changed password.");
    if ((in_array("enabled", array_keys($e->arguments["old_values"])))
    		&& ($e->arguments["old_values"]["enabled"] != $u->enabled))
    	CM5_Logger::getInstance()->notice("User \"{$u->username}\" was " . ($u->enabled?"enabled":"disabled") . ".");
});

CM5_Model_User::one_to_many('CM5_Model_Page', 'user', 'articles');
CM5_Model_Group::many_to_many('CM5_Model_User', 'CM5_Model_Membership', 'groups', 'users');

