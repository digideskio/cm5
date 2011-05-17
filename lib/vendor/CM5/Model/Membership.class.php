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
 * Model class for memberships table.
 * 
 * @author sque@0x0lab.org
 *
 * @property string $username
 * @property string $groupname
 * 
 * Relations:
 * @property CM5_Model_User $user
 * @property CM5_Model_Group $group
 */
class CM5_Model_Membership extends DB_Record
{
    static public function get_table()
    {   
        return CM5_Config::get_instance()->db->prefix . 'memberships';
    }

    static public $fields = array(
        'username' => array('pk' => true, 'fk' => 'CM5_Model_User'),
        'groupname' => array('pk' => true, 'fk' => 'CM5_Model_Group')
    );
}

CM5_Model_Membership::events()->connect('op.post.create', function($e) {
    // Update last modified
    $m = $e->arguments["record"];
   
    // Log event
    CM5_Logger::get_instance()->notice("User \"{$m->username}\" joined group \"{$m->groupname}\".");
});

CM5_Model_Membership::events()->connect('op.pre.delete', function($e) {
    // Update last modified
    $m = $e->arguments["record"];
   
    // Log event
    CM5_Logger::get_instance()->notice("User \"{$m->username}\" parted group \"{$m->groupname}\".");
});
