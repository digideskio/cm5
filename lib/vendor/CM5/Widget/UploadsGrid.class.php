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
 * Table with all uploads
 */
class CM5_Widget_UploadsGrid extends Output_HTML_Grid
{
	/**
	 * @param array $uploads array with CM5_Upload objects.
	 */
	public function __construct($uploads)
	{
		$this->uploads = $uploads;
		parent::__construct(
		array(
			'id' => array('caption' => 'ID'),
			'filename' => array('caption' => 'Filename', 'customdata' => 'true'),
			'info' => array('caption' => 'Info', 'customdata' => 'true'),
			'tools' => array('caption' => 'Actions', 'customdata' => 'true')
		),
		array(
			'css' => array('ui-grid', 'ui-grid-uploads'),
			'maxperpage' => '10',
			'pagecontrolpos' => 'both'
		),
		$this->uploads
		);
	}

	public function on_custom_data($col_id, $row_id, $record)
	{
		if ($col_id == 'info')
		{
			$res = tag('ul class="info"');
			if ($record->is_image)
			{
				$res->append(tag('li', 'Image size: ',
				tag('span class="imagesize"', "{$record->image_width} x {$record->image_height}")));
			}
			$res->append(tag('li', 'File size: ',
			tag('span class="size"', html_human_fsize($record->filesize, ''))));
			$res->append(tag('li', 'Author: ',
			tag('span class="author"', $record->uploader)));
			$res->append(tag('li', 'Last updated: ',
			tag('span class="updated"', date_exformat($record->lastmodified)->human_diff())));

			return $res;
		}
		else if ($col_id == 'tools')
		{
			return tag('ul class="actions"',
			tag('li',
			UrlFactory::craft('upload.edit', $record->id)->anchor('Edit')->add_class('edit')),
			tag('li',
			UrlFactory::craft('upload.delete',  $record->id)->anchor('Delete')->add_class('delete'))
			);
			return $res;
		}
		else if ($col_id == 'filename')
		{
			$res = '';
			if ($record->is_image)
			$res .= tag('img class="thumb"',
			array('alt' => $record->filename),
			array('src' => UrlFactory::craft('upload.thumb', $record))
			);

			$res .= tag('input readonly="readonly" class="url"',
				array('value' => (string)UrlFactory::craft('upload.view', $record),
					'size' => strlen((string)UrlFactory::craft('upload.view', $record))));
			$res .= UrlFactory::craft('upload.view', $record)->anchor('link')->add_class('download');
			$res .= tag('p', $record->description);
			return $res;
		}
	}

	public function on_mangle_data($col_id, $row_id, $data)
	{

	}
}
