<?php

Layout::open('admin')->get_document()->title = Config::get('site.title') . " | Modules panel";
Stupid::add_rule('module_action',
    array('type' => 'url_path', 'chunk[3]' => '/@([\w\-]+)/', 'chunk[4]' => '/([\w\-]+)/')
);
    
Stupid::set_default_action('show_modules');
Stupid::chain_reaction();


function show_modules()
{
    Layout::open('admin')->activate();

    $grid = new UI_ModulesGrid(CMS_Core::get_instance()->modules());
    etag('div',
        $grid->render()
    );
}

function module_action($module_name, $action)
{
    Layout::open('admin')->activate();
    
    if (($module = CMS_Core::get_instance()->get_module($module_name)) === null)
        not_found();

    if (($action = $module->get_action($action)) === null)
        not_found();

    Layout::open('admin')->get_submenu()->create_entry($module->info_property('title'));
    foreach($module->get_actions() as $a)
    {
        Layout::open('admin')->get_submenu()->create_link(
            $a['display'],
            ''
        )->set_link(UrlFactory::craft('module.action', $module->info_property('nickname'), $a['name']), true);
    }
    
    etag('div class="contents"')->push_parent();
    call_user_func($action['callback']);
    Output_HTMLTag::pop_parent();

}
?>
