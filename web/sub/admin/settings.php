<?php


Stupid::set_default_action('view_settings');
Stupid::chain_reaction();

function view_settings()
{
    Layout::open('admin')->activate();
    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . 
        " | System settings";
        
    $frm = new UI_SystemSettings();
    etag('div', $frm->render());
}

?>
