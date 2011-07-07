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

require_once __DIR__ . '/lib/ExportForm.class.php';
require_once __DIR__ . '/lib/UploadForm.class.php';
require_once __DIR__ . '/lib/ImportForm.class.php';
require_once __DIR__ . '/lib/FixLinks.class.php';



class CM5_Module_Migration extends CM5_Module
{
	//! Initialize module
	public function onInitialize()
	{
		$this->declareAction('import', 'Import', 'request_import');
		$this->declareAction('export', 'Export', 'request_export');
		$this->declareAction('fixlinks', 'Fix links', 'request_fixlinks');
	}

	public function request_import()
	{
		if (($fid = Net_HTTP_RequestParam::get('fid')) !== null)
		{
			if (($upload = CM5_Model_Upload::open($fid)) === false)
			throw new Exception404();
			$frm = new CM5_Module_Migration_ImportForm($upload);
			if ($frm->process() == Form::RESULT_VALID) {
				etag('div class="message"',
		            'Import was done completed succesfully. You can now ',
				tag('a', 'delete')->attr('href', (string)UrlFactory::craft('upload.delete', $frm->archive_upload->id)),
		            ' uploaded archive if you wish.'
		            );
			}
			else
			etag('div', $frm->render());
		}
		else
		{
			$frm = new CM5_Module_Migration_UploadForm();
			etag('div', $frm->render());
			if ($frm->upload_id !== null)
			{
				$url = UrlFactory::craft('module.action', $this->getMetaInfoEntry('nickname'), 'import') . '?fid=' . $frm->upload_id;
				Net_HTTP_Response::redirect($url);
			}
		}
	}

	public function request_export()
	{
		$frm = new CM5_Module_Migration_ExportForm();
		etag('div', $frm->render());
	}

	public function request_fixlinks()
	{
		$frm = new CM5_Module_Migration_FixLinks();
		etag('div', $frm->render());
	}
}

return array(
	'class' => 'CM5_Module_Migration',
	'nickname' => 'migration',
	'title' => 'Import/Export pages',
	'description' => 'Add support for importing and exporting pages.'
);
