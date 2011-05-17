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

Layout::open('admin')->get_document()->title = CM5_Config::get_instance()->site->title . " | Modules panel";
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

    $grid = new CM5_Widget_ModulesGrid(CM5_Core::get_instance()->modules());
    etag('div',
        $grid->render()
    );
}

function module_action($module_name, $action)
{
    Layout::open('admin')->activate();
    
    if (($module = CM5_Core::get_instance()->get_module($module_name)) === null)
        not_found();

    if (($action = $module->get_action($action)) === null)
        not_found();

    Layout::open('admin')->get_document()->title = CM5_Config::get_instance()->site->title . 
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
    
    if (($module = CM5_Core::get_instance()->get_module($module_name)) === null)
        not_found();

    Layout::open('admin')->get_document()->title = CM5_Config::get_instance()->site->title . 
        " | Module: {$module->info_property('title')} > Configure";
        
    $frm = new CM5_Form_ModuleConfigure($module);
    etag('div',  $frm->render());
}

function module_enable($module_name)
{

    Layout::open('admin')->activate();
    
    if (($module = CM5_Core::get_instance()->get_module($module_name)) === null)
        not_found();
        
    Layout::open('admin')->get_document()->title = CM5_Config::get_instance()->site->title . 
        " | Module: {$module->info_property('title')} > Enable";
        
    $frm = new CM5_Form_Confirm(
        'Module: ' . $module->info_property('title'),
        'Are you sure you want to enable this module?',
        'Enable',
        create_function('$m', '
            CM5_Core::get_instance()->enable_module($m->config_nickname());
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
    
    if (($module = CM5_Core::get_instance()->get_module($module_name)) === null)
        not_found();
        
    Layout::open('admin')->get_document()->title = CM5_Config::get_instance()->site->title . 
        " | Module: {$module->info_property('title')} > Disable";
        
    $frm = new CM5_Form_Confirm(
        'Module: ' . $module->info_property('title'),
        'Are you sure you want to disable this module?',
        'Disable',
        create_function('$m', '
            CM5_Core::get_instance()->disable_module($m->config_nickname());
            UrlFactory::craft("module.admin")->redirect();
        '),
        array($module),
        UrlFactory::craft("module.admin")
    );
    etag('div',  $frm->render());
}
?>
