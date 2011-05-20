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
 * Table with all users.
 */
class CM5_Widget_UsersGrid extends Output_HTML_Grid
{
	/**
	 * @param array $users array of CM5_Model_User users.
	 */
	public function __construct($users)
	{
		$this->users = $users;
		parent::__construct(
		array(
			'status' => array('caption' => 'Status', 'customdata' => 'true'),
			'username' => array('caption' => 'Username'),
			'groups' => array('caption' => 'Groups', 'customdata' => true),
			'tools' => array('caption' => 'Tools', 'customdata' => 'true'),
		),
		array(
			'css' => array('ui-grid', 'users')
		),
		$this->users
		);
	}

	public function on_custom_data($col_id, $row_id, $user, $tr)
	{
		if ($col_id == 'status')
		{
			/*$check = tag('input type="checkbox" disabled="disabled"');
			if ($user->enabled)
			$check->attr('checked', 'true');
			return $check;*/
			if ($user->enabled) {
				return 'enabled';
			} else {
				$tr->add_class('disabled');
				return 'disabled';
			}
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
