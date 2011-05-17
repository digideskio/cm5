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
 * Grid with all log events.
 */
class CM5_Widget_LogGrid extends Output_HTML_Grid
{
	/**
	 * @param array $entries array of log entries.
	 */
	public function __construct($entries)
	{
		$this->entries = $entries;
		parent::__construct(
		array(
			'id' => array(),
			'priorityName' => array('caption' => 'Priority', 'customdata' => true),
			'message' => array('caption' => 'Message'),
			'timestamp' => array('caption' => 'Time', 'customdata' => 'true'),
			'user' => array('caption' => 'User'),
			'ip' => array('caption' => 'IP'),
		),
		array(
			'css' => array('ui-grid', 'ui-grid-log'),
			'maxperpage' => '50',
			'pagecontrolpos' => 'both'
		),
		$this->entries
		);
	}

	public function on_custom_data($col_id, $row_id, $record)
	{
		if ($col_id == 'timestamp')
		{
			return date_exformat($record->timestamp)->human_diff();
		}
		if ($col_id == 'priorityName')
		{
			return (string)tag('span class="priority"', $record->priorityName)->add_class(strtolower($record->priorityName));
		}
	}

	public function on_mangle_data($col_id, $row_id, $data)
	{

	}
}
