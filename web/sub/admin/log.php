<?php

Stupid::set_default_action('show_log');
Stupid::chain_reaction();

function show_log()
{
    Layout::open('admin')->activate();
   
    $log = new UI_LogGrid(Log::open_query()->order_by('id', 'DESC')->execute());
    etag('div', $log->render());
}
?>
