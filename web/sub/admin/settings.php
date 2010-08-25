<?php


Stupid::set_default_action('view_settings');
Stupid::chain_reaction();

function view_settings()
{
    Layout::open('admin')->activate();

    $frm = new UI_SystemSettings();
    etag('div', $frm->render());
}

?>
