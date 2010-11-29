<?php

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
    if (!($f = Upload::open($id)))
        not_found();

    // Check cache control
    check_client_cache($f->lastmodified);

    // Add expire header
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $f->lastmodified->format('U')) . ' GMT' );
    
    $f->dump_file();
}

function dump_file_by_name($name)
{
    $files = Upload::raw_query()->select(array('id'))->where('filename = ?')->execute($name);
    if (count($files) !== 1)
        not_found();
    
    dump_file_by_id($files[0]['id']);
}

function image_thumbnail_by_id($id)
{
    if (!($f = Upload::open($id)))
        not_found();

    // Check cache control
    check_client_cache($f->lastmodified);

    // Add expire header
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $f->lastmodified->format('U')) . ' GMT' );
    
    $f->dump_thumb();
}

function image_thumbnail_by_name($name)
{
    $files = Upload::raw_query()->select(array('id'))->where('filename = ?')->execute($name);
    if (count($files) !== 1)
        not_found();
    
    image_thumbnail_by_id($files[0]['id']);
}
?>
