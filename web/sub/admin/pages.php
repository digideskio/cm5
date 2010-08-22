<?

Stupid::add_rule('move_page',
    array('type' => 'url_path', 'chunk[3]' => '/([\d]+)/', 'chunk[4]' => '/\+move/')
);
Stupid::add_rule('delete_page',
    array('type' => 'url_path', 'chunk[3]' => '/([\d]+)/', 'chunk[4]' => '/\+delete/')
);
Stupid::add_rule('edit_page',
    array('type' => 'url_path', 'chunk[3]' => '/([\d]+)/')
);
Stupid::add_rule('create_page',
    array('type' => 'url_path', 'chunk[3]' => '/\+create/')
);
Stupid::set_default_action('pages_default');
Stupid::chain_reaction();

function __draw_tree_entry($p, $current_page_id)
{
    if ($p['system'])
        $li = tag('li class="system-page"',
            $pg = UrlFactory::craft('page.edit', $p['id'])->anchor($p['title'])->add_class('page')->add_class($p['status'])
        );
    else
    {
        $li = tag('li class="user-page" id="page_' . $p['id'] . ' ',
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

    foreach($p['childs'] as $sp)
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

    foreach(CMS_Core::get_instance()->get_tree() as $p)
    {
        $ul->append(__draw_tree_entry($p, $current_page_id));
    }

}

function edit_page($id)
{
    Layout::open('admin')->activate();
    Layout::open('admin')->get_document()->add_ref_js(surl('/static/ckeditor/ckeditor.js'));
    Layout::open('admin')->get_document()->add_ref_js(surl('/static/js/admin-pagemenu.js'));
    
    if (!$p = Page::open($id))
        not_found();
    Layout::open('admin')->get_document()->title = Config::get('site.title') . " | Edit: {$p->title}";
    
    $frm = new UI_EditPage($p);
    
    show_pages_tree($id);
    etag('div id="page_editor"', $frm->render());
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
        UrlFactory::craft('page.edit', $p[0]->id)->redirect();
    else
        UrlFactory::craft('page.create', null)->redirect();
}
?>
