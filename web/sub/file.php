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
        
    $f->dump_file();
}

function image_thumbnail($id)
{
    if (!($f = Upload::open($id)))
        not_found();
        
    $img = new Image(Config::get('site.upload_folder') . '/' . $f->store_file);

    $img->resize(50, 50)
        ->dump(array('quality' => '91'));
}
?>
