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

    class UI_UsersGrid extends Output_HTML_Grid
    {
        public function __construct($users)
        {
            $this->users = $users;
            parent::__construct(
                array(
                    'enabled' => array('caption' => 'Enabled', 'customdata' => 'true'),
                    'username' => array('caption' => 'Username'),
                    'groups' => array('caption' => 'Groups', 'customdata' => true),
                    'tools' => array('caption' => 'Tools', 'customdata' => 'true'),
                ),
                array(
                ), 
                $this->users
            );
        }
        
        public function on_custom_data($col_id, $row_id, $user)
        {
            if ($col_id == 'enabled')
            {
                $check = tag('input type="checkbox" disabled="disabled"');
                if ($user->enabled)
                    $check->attr('checked', 'true');
                return $check;
            }
            else if ($col_id == 'groups')
            {
                $groups = array();
                foreach($user->groups->all() as $g)
                    $groups[] = $g->groupname;

                return implode(', ', $groups);
            }
            else if ($col_id == 'tools')
            {
                return tag('ul class="actions"',
                    tag('li',
                        UrlFactory::craft('user.edit', $user->username)->anchor('Edit')->add_class('edit')),
                    tag('li',
                        UrlFactory::craft('user.delete',  $user->username)->anchor('Delete')->add_class('delete'))
                );
                return $res;
            }
        }
    }

?>
