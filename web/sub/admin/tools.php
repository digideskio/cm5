<?php

Stupid::add_rule('tool_translit',
    array('type' => 'url_path', 'chunk[3]' => '/transliterate/'),
    array('type' => 'url_params', 'op' => 'isset', 'param' => 'text', 'param_type' => 'both')
);

Stupid::add_rule('pages_tree',
    array('type' => 'url_path', 'chunk[3]' => '/^pages_tree$/')
);
Stupid::set_default_action('default_tools');
Stupid::chain_reaction();

function tool_translit()
{
    $str = Net_HTTP_RequestParam::get('text');
	echo transliterate($str);
}

function pages_ext_tree_loader($tree)
{
	foreach($tree as & $e)
	{
		$e['text'] = $e['title'];
		$e['cls'] = 'page';
		if ($e['status'] == 'draft')
			$e['cls'] .= ' draft';
			
		if ($e['system'])
		{
			$e['draggable'] = false;
			$e['leaf'] = true;
		}
			
		$e['children'] = pages_ext_tree_loader($e['children']);		
	}
	unset($e);
	return $tree;
}

function pages_tree()
{
	header('Content-type: text/plain; charset=UTF-8');
	$tree = CM5_Core::get_instance()->get_tree();
	$tree = pages_ext_tree_loader($tree);
	echo json_encode($tree);
}

function default_tools()
{
}