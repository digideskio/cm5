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
 * Model class for log table.
 * 
 * @author sque@0x0lab.org
 *
 * @property integer $id
 * @property DateTime $timestamp
 * @property string $message
 * @property string $priority
 * @property string $priorityName
 * @property string $user
 * @property string $ip 
 */
class CM5_Model_Log extends DB_Record
{
    static public function get_table()
    {   
        return CM5_Config::get_instance()->db->prefix . 'log';
    }

    static public $fields = array(
        'id' => array('pk' => true, 'ai' => true),
        'timestamp' => array('type' => 'datetime'),
        'message',
        'priority',
        'priorityName',
        'user',
        'ip'
    );
    
    static public function reset()
    {
        CM5_Model_Log::raw_query()->delete()->execute();
        CM5_Logger::get_instance()->warn("Log was reseted.");
    }
}

