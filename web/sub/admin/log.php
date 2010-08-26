<?php

Stupid::add_rule('clear_log',
    array('type' => 'url_path', 'chunk[3]' => '/^\+clear$/')
);
Stupid::set_default_action('show_log');
Stupid::chain_reaction();

function show_log()
{
    Layout::open('admin')->activate();

    // Filter control panel
    $priorities = array(
        'INFO' => 'Info',
        'NOTICE' => 'Notice',
        'WARN' => 'Warning',
        'ERR' => 'Error',
        'CRIT' => 'Critical',
        'ALERT' => 'Alert',
        'EMERG' => 'Emergency'
    );


    if ($filters_on = Net_HTTP_RequestParam::get('priorites'))
    {
        $filters_enabled = array();
        foreach(explode(',', $filters_on) as $f)
            if (in_array($f, array_keys($priorities)))
                $filters_enabled[] = $f;
        $filters_enabled = array_unique($filters_enabled);
    }
    else
        $filters_enabled = array_keys($priorities);
        
    function create_filter_link($priority, $current_filter)
    {
        if (in_array($priority, $current_filter))
        {   
            // Remove filter
            if(($k = array_search($priority, $current_filter)) !== false)
                unset($current_filter[$k]);
        }
        else
        {   // Add filter
            $current_filter[] = $priority;
        }
        return (string)UrlFactory::craft('log.view_filtered', implode(',', $current_filter));
    }
    $panel = etag('div', 
        $controls = tag('ul class="filters'),
        UrlFactory::craft('log.clear')->anchor('Clear')->add_class('button delete clear-log')
    )->add_class('panel log');
    foreach($priorities as $id => $name)
    {
        tag('li', $anchor = tag('a class="button"', $name))->appendto($controls);
        if (in_array($id, $filters_enabled))
            $anchor->add_class('pressed');
        $anchor->attr('href', create_filter_link($id, $filters_enabled));
    }

    // Show list
    $log_query = Log::open_query()->order_by('id', 'DESC');
    foreach($filters_enabled as $id)
        $log_query->where('priorityName = ?', 'OR')->push_exec_param($id);
    $log = new UI_LogGrid($log_query->execute());
    etag('div', $log->render());
}

function clear_log()
{
    Layout::open('admin')->activate();
    $frm = new UI_ConfirmForm(
        'Clear system log',
        'Are you sure? This action is inreversible!',
        'Clear',
        create_function('','
            Log::reset();        
            UrlFactory::craft("log.view")->redirect();'
        ),
        array(),
        UrlFactory::craft("log.view")
    );
    etag('div', $frm->render());
}
?>
