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

class CM5_Module_Revisions extends CM5_Module
{
     //! The name of the module
    public function info()
    {
        return array(
            'nickname' => 'revisions',
            'title' => 'Revisions Tracking',
            'description' => 'Add support for creating pages revisions.'
        );
    }
    
    //! Initialize module
    public function init()
    {
    	// Adding model add hooks also
    	require dirname(__FILE__) . '/lib/Revision.class.php';
    }
    
    public function on_enable()
    {
		$dbprefix = GConfig::get_instance()->db->prefix;
		if (DB_Conn::get_link()->multi_query(require(dirname(__FILE__) . '/install/build-script.php')))
			while (DB_Conn::get_link()->next_result());    	
    }
}

CM5_Module_Revisions::register();
