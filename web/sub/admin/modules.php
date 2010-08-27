<?php

Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . " | Modules panel";
Stupid::add_rule('module_action',
    array('type' => 'url_path', 'chunk[3]' => '/@([\w\-]+)/', 'chunk[4]' => '/([\w\-]+)/')
);
Stupid::add_rule('module_configure',
    array('type' => 'url_path', 'chunk[3]' => '/([\w\-]+)/', 'chunk[4]' => '/\+configure/')
);
Stupid::add_rule('module_enable',
    array('type' => 'url_path', 'chunk[3]' => '/([\w\-]+)/', 'chunk[4]' => '/\+enable/')
);
Stupid::add_rule('module_disable',
    array('type' => 'url_path', 'chunk[3]' => '/([\w\-]+)/', 'chunk[4]' => '/\+disable/')
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

    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . 
        " | Module: {$module->info_property('title')} > {$action['display']}";
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

function module_configure($module_name)
{
    Layout::open('admin')->activate();
    
    if (($module = CMS_Core::get_instance()->get_module($module_name)) === null)
        not_found();

    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . 
        " | Module: {$module->info_property('title')} > Configure";
        
    $frm = new UI_ModuleConfigure($module);
    etag('div',  $frm->render());
}

function module_enable($module_name)
{

    Layout::open('admin')->activate();
    
    if (($module = CMS_Core::get_instance()->get_module($module_name)) === null)
        not_found();
        
    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . 
        " | Module: {$module->info_property('title')} > Enable";
        
    $frm = new UI_ConfirmForm(
        'Module: ' . $module->info_property('title'),
        'Are you sure you want to enable this module?',
        'Enable',
        create_function('$m', '
            CMS_Core::get_instance()->enable_module($m->config_nickname());
            UrlFactory::craft("module.admin")->redirect();
        '),
        array($module),
        UrlFactory::craft("module.admin")
    );
    etag('div',  $frm->render());
}

function module_disable($module_name)
{

    Layout::open('admin')->activate();
    
    if (($module = CMS_Core::get_instance()->get_module($module_name)) === null)
        not_found();
        
    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . 
        " | Module: {$module->info_property('title')} > Disable";
        
    $frm = new UI_ConfirmForm(
        'Module: ' . $module->info_property('title'),
        'Are you sure you want to disable this module?',
        'Disable',
        create_function('$m', '
            CMS_Core::get_instance()->disable_module($m->config_nickname());
            UrlFactory::craft("module.admin")->redirect();
        '),
        array($module),
        UrlFactory::craft("module.admin")
    );
    etag('div',  $frm->render());
}
?>
