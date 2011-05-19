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

Stupid::add_rule('upload_file',
	array('type' => 'url_path', 'chunk[3]' => '/^\+upload$/')
);
Stupid::add_rule('edit_file',
	array('type' => 'url_path', 'chunk[3]' => '/^([\d]+)$/', 'chunk[4]' => '/^\+edit$/')
);
Stupid::add_rule('delete_file',
	array('type' => 'url_path', 'chunk[3]' => '/^([\d]+)$/', 'chunk[4]' => '/^\+delete$/')
);
Stupid::set_default_action('show_files');
Stupid::chain_reaction();


function upload_file()
{
	$frm = new CM5_Form_UploadCreate();
	etag('div',
	$frm->render());
}

function edit_file($id)
{
	if (!($u = CM5_Model_Upload::open($id)))
		throw new Exception404();

	Layout::getActive()->getDocument()->title = CM5_Config::getInstance()->site->title .
        " | File: {$u->filename} > Edit";

	$frm = new CM5_Form_UploadEdit($u);

	etag('div', $frm->render());
}

function delete_file($id)
{
	if (!($u = CM5_Model_Upload::open($id)))
	throw new Exception404();

	Layout::getActive()->getDocument()->title = CM5_Config::getInstance()->site->title .
        " | File: {$u->filename} > Delete";
	$frm = new CM5_Form_Confirm(
        "Delete \"{$u->filename}\"",
        'Are you sure? This action is inreversible!',
        'Delete',
		function($u) {
			$u->delete();
			UrlFactory::craft("upload.admin")->redirect();
		},
		array($u),
		UrlFactory::craft("upload.admin")
	);

	etag('div', $frm->render());
}

function show_files()
{
	etag('div',
		UrlFactory::craft('upload.create')->anchor('Upload a new file')
			->add_class('button download strong')
	)->add_class('panel uploads');
	$tab = new CM5_Widget_UploadsGrid(CM5_Model_Upload::open_query()->order_by('id', 'DESC')->execute());
	etag('div',  $tab->render());
}
