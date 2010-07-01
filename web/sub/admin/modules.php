<?php

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
