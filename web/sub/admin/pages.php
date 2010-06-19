<?

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
Stupid::set_default_action('pages_default');
Stupid::chain_reaction();

function show_pages_tree()
{
    $draw_tree_entry = function($p) use(&$draw_tree_entry)
    {
        $li = tag('li id="page_' . $p->id . ' class=""',
            UrlFactory::craft('page.edit', $p)->anchor($p->title . ' ')->add_class('edit')->add_class($p->status),
            $ul = tag('ul class="sortable"'),
            UrlFactory::craft('page.create', $p->id)->anchor('add subpage')->add_class('add')
        );

        foreach($p->subpages->subquery()->order_by('order', 'DESC')->execute() as $sp)
            $ul->append($draw_tree_entry($sp));

        return $li;
    };
    
    // Create page tree
    etag('div id="pages_tree"', 
        tag('span class="title"', 'Pages tree'),
        tag('span class="resort"', 'resort')->add_class('button'),
        $ul = tag('ul class="sortable"'),
        UrlFactory::craft('page.create', '')->anchor('add page')->add_class('add')
    );

    foreach(Page::open_query()->where('parent_id is null')->execute() as $p)
    {
        $ul->append($draw_tree_entry($p));
    }
}

function edit_page($id)
{
    Layout::open('admin')->activate();
    Layout::open('admin')->get_document()->add_ref_js(surl('/static/ckeditor/ckeditor.js'));
    Layout::open('admin')->get_document()->add_ref_js(surl('/static/js/admin-pagemenu.js'));
    
    if (!$p = Page::open($id))
        not_found();

    $frm = new UI_EditPage($p);
    
    show_pages_tree();
    etag('div id="page_editor"', $frm->render());
}

function create_page()
{
    Layout::open('admin')->activate();
    Layout::open('admin')->get_document()->add_ref_js(surl('/static/js/admin-pagemenu.js'));
    
    $parent_id = Net_HTTP_RequestParam::get('parent', 'get');
    if ($parent_id === '')
        $parent_id = null;
    $frm = new UI_CreatePage($parent_id);
    etag('div', $frm->render());
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

    $p->parent_id = $parent_id;
    $p->save();
}

function pages_default()
{
    Layout::open('admin')->activate();
    
    $p = Page::open_query()->limit(1)->execute();
    if (count($p))
        UrlFactory::craft('page.edit', $p[0])->redirect();
    else
        UrlFactory::craft('page.create', null)->redirect();
}
?>
