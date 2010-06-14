<?php


// Use default layout to render this page
Layout::open('default')->get_document()->title = 'Telos';
Layout::open('default')->get_document()->add_ref_js(surl('/static/ckeditor/ckeditor.js'));
Layout::open('default')->get_document()->add_ref_js(surl('/static/js/jquery-1.4.2.min.js'));
Layout::open('default')->get_document()->add_ref_js(surl('/static/js/jquery-ui-1.8.2.custom.min.js'));
Layout::open('default')->get_document()->add_ref_js(surl('/static/js/admin-pagemenu.js'));

Stupid::add_rule('move_page',
    array('type' => 'url_path', 'chunk[2]' => '/page/', 'chunk[3]' => '/([\d]+)/', 'chunk[4]' => '/\+move/'),
    array('type' => 'url_params', 'op' => 'isset', 'param' => 'parent_id', 'param_type' => 'post')
);
Stupid::add_rule('edit_page',
    array('type' => 'url_path', 'chunk[2]' => '/page/', 'chunk[3]' => '/([\d]+)/')
);
Stupid::add_rule('create_page',
    array('type' => 'url_path', 'chunk[2]' => '/page/', 'chunk[3]' => '/\+create/')
);

Stupid::add_rule('tool_translit',
    array('type' => 'url_path', 'chunk[2]' => '/tools/', 'chunk[3]' => '/transliterate/'),
    array('type' => 'url_params', 'op' => 'isset', 'param' => 'text', 'param_type' => 'both')
);
Stupid::set_default_action('admin_panel');
Stupid::chain_reaction();

function show_pages_tree()
{
    $draw_tree_entry = function($p) use(&$draw_tree_entry)
    {
        $li = tag('li id="page_' . $p->id . ' class=""',
            UrlFactory::craft('page.edit', $p)->anchor($p->title . ' ')->add_class('edit'),
            $ul = tag('ul " class="sortable"'),
            UrlFactory::craft('page.create', $p->id)->anchor('add child')->add_class('add')
        );

        foreach($p->subpages->all() as $sp)
            $ul->append($draw_tree_entry($sp));

        return $li;
    };
    
    // Create page tree
    etag('div id="pages_tree"', 
        tag('span class="resort-button"', '(resort)'),
        $ul = tag('ul class="sortable"'),
        UrlFactory::craft('page.create', '')->anchor('add child')->add_class('add')
    );

    foreach(Page::open_query()->where('parent_id is null')->execute() as $p)
    {
        $ul->append($draw_tree_entry($p));
    }
}

function edit_page($id)
{
    Layout::open('default')->activate();
    if (!$p = Page::open($id))
        not_found();

    show_pages_tree();
        
    $frm = new UI_EditPage($p);
    etag('div id="page_editor', $frm->render());
}

function create_page()
{
    Layout::open('default')->activate();
    $frm = new UI_CreatePage();
    etag('div id="page_editor', $frm->render());
}

function move_page($page_id)
{
    sleep(1);   //artificial lag!
    
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

    $p->parent_id = $parent_id;
    $p->save();
}

function admin_panel()
{
    Layout::open('default')->activate();
    etag('a', 'Edit page', array('href' => url('/admin/page/1')));
}

function tool_translit()
{
    $str = Net_HTTP_RequestParam::get('text');
	echo transliterate($str);;
}
?>
