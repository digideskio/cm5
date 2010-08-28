<?php

Stupid::add_rule('theme_configure',
    array('type' => 'url_path', 'chunk[3]' => '/([\w\-]+)/', 'chunk[4]' => '/\+configure/')
);
Stupid::add_rule('theme_switch',
    array('type' => 'url_path', 'chunk[3]' => '/([\w\-]+)/', 'chunk[4]' => '/\+switch/')
);
Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . " | Themes panel";
Stupid::set_default_action('show_themes');
Stupid::chain_reaction();


function show_themes()
{
    Layout::open('admin')->activate();
    $grid = new UI_ModulesGrid(CM5_Core::get_instance()->theme_modules(), true);
    etag('div',
        $grid->render()
    );
}

function theme_switch($theme_name)
{
    Layout::open('admin')->activate();
    
    CM5_Core::get_instance()->load_themes();
    if (($theme = CM5_Core::get_instance()->get_module($theme_name)) === null)
        not_found();
        
    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . 
        " | Theme: {$theme->info_property('title')} > Switch";
        
    $frm = new UI_ConfirmForm(
        'Theme: ' . $theme->info_property('title'),
        'Are you sure you want to switch to this theme?',
        'Switch',
        create_function('$name', '
            $config = GConfig::get_writable_copy();
            $config->site->theme = $name;
            GConfig::update($config);
            CM5_Core::get_instance()->invalidate_page_cache(null);
            UrlFactory::craft("theme.admin")->redirect();
        '),
        array($theme_name),
        UrlFactory::craft("theme.admin")
    );
    etag('div',  $frm->render());
}

function theme_configure($theme_name)
{
    Layout::open('admin')->activate();
    
    CM5_Core::get_instance()->load_themes();
    if (($theme = CM5_Core::get_instance()->get_module($theme_name)) === null)
        not_found();

    Layout::open('admin')->get_document()->title = GConfig::get_instance()->site->title . 
        " | Theme: {$theme->info_property('title')} > Configure";
        
    $frm = new UI_ModuleConfigure($theme);
    etag('div',  $frm->render());
}
?>
