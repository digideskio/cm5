<?php

Stupid::add_rule('upload_file',
    array('type' => 'url_path', 'chunk[3]' => '/^\+upload$/')
);
Stupid::add_rule('edit_file',
    array('type' => 'url_path', 'chunk[3]' => '/^([\d]+)$/', 'chunk[4]' => '/^\+edit$/')
);
Stupid::add_rule('delete_file',
    array('type' => 'url_path', 'chunk[3]' => '/^([\d]+)$/', 'chunk[4]' => '/^\+delete$/')
);
Stupid::set_default_action('show_modules');
Stupid::chain_reaction();


function show_modules()
{
    Layout::open('admin')->activate();

    $grid = new UI_ModulesGrid(CMS_Core::get_instance()->modules());
    etag('div',
        $grid->render()
    );
}
?>
