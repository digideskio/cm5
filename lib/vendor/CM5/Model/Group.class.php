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
 * Model class for groups table.
 * 
 * @author sque@0x0lab.org
 *
 * @property string $groupname
 * 
 * Relations:
 * @property array $users
 */
class CM5_Model_Group extends DB_Record
{
    static public function get_table()
    {   
        return GConfig::get_instance()->db->prefix . 'groups';
    }

    static public $fields = array(
        'groupname' => array('pk' => true)
    );
    
    public function __toString()
    {
    	return $this->groupname;
    }
}
