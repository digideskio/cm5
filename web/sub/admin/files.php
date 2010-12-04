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
    Layout::open('admin')->activate();
    $frm = new UI_UploadFile();
    etag('div',
        $frm->render());
}

function edit_file($id)
{
    Layout::open('admin')->activate();
    if (!($u = CM5_Model_Upload::open($id)))
        not_found();
    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . 
        " | File: {$u->filename} > Edit";
        
    $frm = new UI_UploadEdit($u);
    
    etag('div', $frm->render());
}

function delete_file($id)
{
    Layout::open('admin')->activate();
    if (!($u = CM5_Model_Upload::open($id)))
        not_found();
    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . 
        " | File: {$u->filename} > Delete";
    $frm = new UI_ConfirmForm(
        "Delete \"{$u->filename}\"",
        'Are you sure? This action is inreversible!',
        'Delete',
        create_function('$u','
            $u->delete();
            UrlFactory::craft("upload.admin")->redirect();
        '),
        array($u),
        UrlFactory::craft("upload.admin")
    );
    
    etag('div', $frm->render());
}

function show_files()
{
    Layout::open('admin')->activate();

    etag('div',
        UrlFactory::craft('upload.create')->anchor('Upload a new file')
            ->add_class('button')
            ->add_class('download')
    )->add_class('panel uploads');
    $tab = new UI_UploadsGrid(CM5_Model_Upload::open_query()->order_by('id', 'DESC')->execute());
    etag('div',  $tab->render());
}
