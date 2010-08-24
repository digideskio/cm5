<?php

Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . " | Themes panel";
Stupid::set_default_action('show_themes');
Stupid::chain_reaction();


function show_themes()
{
    Layout::open('admin')->activate();

    $grid = new UI_ThemesGrid(CMS_Core::get_instance()->themes());
    etag('div',
        $grid->render()
    );
}
?>
