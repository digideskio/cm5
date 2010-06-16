<?php


Stupid::add_rule(function(){    require_once(dirname(__FILE__) . '/admin/files.php');    },
    array('type' => 'url_path', 'chunk[2]' => '/^files?$/')
);
Stupid::add_rule(function(){    require_once(dirname(__FILE__) . '/admin/pages.php');    },
    array('type' => 'url_path', 'chunk[2]' => '/^pages?$/')
);
Stupid::add_rule('tool_translit',
    array('type' => 'url_path', 'chunk[2]' => '/tools/', 'chunk[3]' => '/transliterate/'),
    array('type' => 'url_params', 'op' => 'isset', 'param' => 'text', 'param_type' => 'both')
);
Stupid::set_default_action('default_admin_panel');
Stupid::chain_reaction();



function default_admin_panel()
{
    Layout::open('admin')->activate();
    etag('a', 'Edit page', array('href' => url('/admin/page/1')));
}

function tool_translit()
{
    $str = Net_HTTP_RequestParam::get('text');
	echo transliterate($str);;
}
?>
