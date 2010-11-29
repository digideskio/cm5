<?php
/*
 *  This file is part of CM5 <http://code.0x0lab.org/p/cm5>.
 *  
 *  Copyright (c) 2010 Sque.
 *  
 *  CM5 is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published 
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  CM5 is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with CM5.  If not, see <http://www.gnu.org/licenses/>.
 *  
 *  Contributors:
 *      Sque - initial API and implementation
 */

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
