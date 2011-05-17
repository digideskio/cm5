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

Stupid::add_rule('theme_configure',
    array('type' => 'url_path', 'chunk[3]' => '/([\w\-]+)/', 'chunk[4]' => '/\+configure/')
);
Stupid::add_rule('theme_switch',
    array('type' => 'url_path', 'chunk[3]' => '/([\w\-]+)/', 'chunk[4]' => '/\+switch/')
);
Layout::open('admin')->get_document()->title = CM5_Config::get_instance()->site->title . " | Themes panel";
Stupid::set_default_action('show_themes');
Stupid::chain_reaction();


function show_themes()
{
    Layout::open('admin')->activate();
    $grid = new CM5_Widget_ModulesGrid(CM5_Core::get_instance()->theme_modules(), true);
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
        
    Layout::open('admin')->get_document()->title = CM5_Config::get_instance()->site->title . 
        " | Theme: {$theme->info_property('title')} > Switch";
        
    $frm = new CM5_Form_Confirm(
        'Theme: ' . $theme->info_property('title'),
        'Are you sure you want to switch to this theme?',
        'Switch',
        function($name) {
            $config = CM5_Config::get_writable_copy();
            $config->site->theme = $name;
            CM5_Config::update($config);
            CM5_Core::get_instance()->invalidate_page_cache(null);
            UrlFactory::craft("theme.admin")->redirect();
        },
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

    Layout::open('admin')->get_document()->title = CM5_Config::get_instance()->site->title . 
        " | Theme: {$theme->info_property('title')} > Configure";
        
    $frm = new CM5_Form_ModuleConfigure($theme);
    etag('div',  $frm->render());
}
