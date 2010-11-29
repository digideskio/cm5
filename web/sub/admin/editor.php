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

Stupid::add_rule('page_editor_form',
    array('type' => 'url_path', 'chunk[3]' => '/([\d]+)/', 'chunk[4]' => '/\+form/')
);
Stupid::add_rule('move_page',
    array('type' => 'url_path', 'chunk[3]' => '/([\d]+)/', 'chunk[4]' => '/\+move/')
);
Stupid::add_rule('delete_page',
    array('type' => 'url_path', 'chunk[3]' => '/([\d]+)/', 'chunk[4]' => '/\+delete/')
);
Stupid::add_rule('create_page',
    array('type' => 'url_path', 'chunk[3]' => '/\+create/')
);
Stupid::set_default_action('pages_default');
Stupid::chain_reaction();

function __draw_tree_entry($p, $current_page_id)
{
    if ($p['system'])
        $li = tag('li class="system-page" id="page_' . $p['id'] . '"',
            $pg = UrlFactory::craft('page.edit', $p['id'])->anchor($p['title'])->add_class('page')->add_class($p['status'])
        );
    else
    {
        $li = tag('li class="user-page" id="page_' . $p['id'] . '" ',
            $pg = UrlFactory::craft('page.edit', $p['id'])->anchor($p['title'])->add_class('page')->add_class($p['status']),
            tag('a html_escape_off class="delete"', '&nbsp;', 
                array(
                    'href' => UrlFactory::craft('page.delete', $p['id']),
                    'title' => 'Delete this page'
                )
            ),
            tag('a html_escape_off class="add"', '&nbsp;', 
                array(
                    'href' => UrlFactory::craft('page.create', $p['id']),
                    'title' => 'Add subpage'
                )
            ),
            $ul = tag('ul class="sortable"')
        );
    }
            
    if ($current_page_id == $p['id'])
        $pg->add_class('selected');

    foreach($p['children'] as $sp)
        $ul->append(__draw_tree_entry($sp, $current_page_id));

    return $li;
}

function show_pages_tree($current_page_id)
{
    // Create page tree
    etag('div id="pages_tree"', 
        tag('span class="title"', 'Pages tree'),
        tag('span class="resort"', 'edit')->add_class('button'),
        $ul = tag('ul class="sortable"'),
        UrlFactory::craft('page.create', '')->anchor('add page')->add_class('add')
    );

    foreach(CM5_Core::get_instance()->get_tree() as $p)
    {
        $ul->append(__draw_tree_entry($p, $current_page_id));
    }

}

function page_editor_form($id)
{
    if (!$p = Page::open($id))
        not_found();

    header('Content-type: text/html; charset=UTF-8');
	$frm = new UI_EditPage($p);
    echo $frm->render();
}

function page_editor()
{
    Layout::open('admin')->activate();
    Layout::open('admin')->get_document()->add_ref_js(surl('/static/js/jquery.ba-hashchange.min.js'));
    Layout::open('admin')->get_document()->add_ref_js(surl('/static/ckeditor/ckeditor.js'));
    Layout::open('admin')->get_document()->add_ref_js(surl('/static/js/admin-pagemenu.js'));
   
    show_pages_tree(null);
    etag('div id="page_editor"');
}

function delete_page($page_id)
{
    if (!($p = Page::open($page_id)))
        not_found();

    Layout::open('admin')->activate();
    $frm = new UI_DeletePage($p);
    etag('div', $frm->render());
}

function create_page()
{
    Layout::open('admin')->activate();
    
    $parent_id = Net_HTTP_RequestParam::get('parent', 'get');
    if ($parent_id === '')
        $parent_id = null;
    $frm = new UI_CreatePage($parent_id);
    etag('div', $frm->render());
    
    etag('script type="text/javascript" html_escape_off',"
    	var request_translit = function() {
			$.get('../tools/transliterate', {
				text : $('.ui-createpage-form input[name=title]').val()
			}, function(data) {
				$('.ui-createpage-form input[name=slug]').val(data);
			});
		};
		
		$(document).ready(function(){
			$('.ui-createpage-form input[name=title]').change(request_translit);
			request_translit();
		});
    ");
}

function move_page($page_id)
{
    if (!($p = Page::open($page_id)))
        not_found();
        
    $parent_id = Net_HTTP_RequestParam::get('parent_id', 'post');
    if ($parent_id === '')
        $parent_id = null;
    else if ($page_id == $parent_id)
    {
        header("HTTP/1.1 501 Internal server error");
        exit;
    }
    else if (!($parent = Page::open($parent_id)))
        not_found();

    // Read slibing page order
    $slibing = Net_HTTP_RequestParam::get('page');
    if ((!is_array($slibing)) || (!in_array($page_id, $slibing)))
    {
        header("HTTP/1.1 501 Internal server error");
        exit;
    }

    // Change parent id
    $p->parent_id = $parent_id;

    // Change order
    $order = 0;
    foreach($slibing as $s)
    {
        if ($s == $page_id)
            $sb = $p;
        else
            $sb = Page::open($s);
        if (!$sb)
        {
            header("HTTP/1.1 501 Internal server error");
            exit;
        }
        $sb->order = $order;
        $sb->save();
        $order+=1;
    }
}

function pages_default()
{
    Layout::open('admin')->activate();
    
    $p = Page::open_query()->limit(1)->execute();
    if (count($p))
    	page_editor();
    else
        UrlFactory::craft('page.create', null)->redirect();
}
?>
