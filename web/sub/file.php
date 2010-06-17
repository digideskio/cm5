<?php

Stupid::add_rule('image_thumbnail',
    array('type' => 'url_path', 'chunk[2]' => '/(\d+)/', 'chunk[3]' => '/^\+thumb$/')
);
Stupid::add_rule('dump_file',
    array('type' => 'url_path', 'chunk[2]' => '/(\d+)/')
);
Stupid::set_default_action('not_found');
Stupid::chain_reaction();

function dump_file($id)
{
    if (!($f = Upload::open($id)))
        not_found();

    // Check cache control
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
    {
        $if_modified_since = date_create($_SERVER['HTTP_IF_MODIFIED_SINCE']);

        if ($if_modified_since->format('U') >= $f->lastupdated->format('U'))
        {
            // Client has latest version
            header( "HTTP/1.1 304 Not Modified" );
            exit;
        }
    }

    // Add expire header
    header('Last-Modified: ' . $f->lastupdated->format('D, d M Y H:i:s'));
    $f->dump_file();
}

function image_thumbnail($id)
{
    if (!($f = Upload::open($id)))
        not_found();

    // Check cache control
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
    {
        $if_modified_since = date_create($_SERVER['HTTP_IF_MODIFIED_SINCE']);

        if ($if_modified_since->format('U') >= $f->lastupdated->format('U'))
        {
            // Client has latest version
            header( "HTTP/1.1 304 Not Modified" );
            exit;
        }
    }

    // Add expire header
    header('Last-Modified: ' . $f->lastupdated->format('D, d M Y H:i:s'));
    
    $f->dump_thumb();
}
?>
