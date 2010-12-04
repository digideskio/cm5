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

Stupid::add_rule('image_thumbnail_by_id',
    array('type' => 'url_path', 'chunk[2]' => '/^\+thumb$/', 'chunk[3]' => '/@(\d+)/')
);
Stupid::add_rule('dump_file_by_id',
    array('type' => 'url_path', 'chunk[2]' => '/@(\d+)/')
);
Stupid::add_rule('image_thumbnail_by_name',
    array('type' => 'url_path', 'chunk[2]' => '/^\+thumb$/', 'chunk[3]' => '/^([^@]{1,255})$/')
);
Stupid::add_rule('dump_file_by_name',
    array('type' => 'url_path', 'chunk[2]' => '/^([^@]{1,255})$/')
);

Stupid::set_default_action('not_found');
Stupid::chain_reaction();

function check_client_cache($lastmodified)
{
    // Check cache control
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
    {
        $if_modified_since = date_create($_SERVER['HTTP_IF_MODIFIED_SINCE']);

        if ($if_modified_since->format('U') >= $lastmodified->format('U'))
        {
            // Client has latest version
            header( "HTTP/1.1 304 Not Modified" );
            exit;
        }
    }
}

function dump_file_by_id($id)
{
    if (!($f = CM5_Model_Upload::open($id)))
        not_found();

    // Check cache control
    check_client_cache($f->lastmodified);

    // Add expire header
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $f->lastmodified->format('U')) . ' GMT' );
    
    $f->dump_file();
}

function dump_file_by_name($name)
{
    $files = CM5_Model_Upload::raw_query()->select(array('id'))->where('filename = ?')->execute($name);
    if (count($files) !== 1)
        not_found();
    
    dump_file_by_id($files[0]['id']);
}

function image_thumbnail_by_id($id)
{
    if (!($f = CM5_Model_Upload::open($id)))
        not_found();

    // Check cache control
    check_client_cache($f->lastmodified);

    // Add expire header
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $f->lastmodified->format('U')) . ' GMT' );
    
    $f->dump_thumb();
}

function image_thumbnail_by_name($name)
{
    $files = CM5_Model_Upload::raw_query()->select(array('id'))->where('filename = ?')->execute($name);
    if (count($files) !== 1)
        not_found();
    
    image_thumbnail_by_id($files[0]['id']);
}
?>
