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

Layout::getActive()->getDocument()->title = CM5_Config::getInstance()->site->title . " | Modules panel";
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
	$grid = new CM5_Widget_ModulesGrid(CM5_Core::getInstance()->getModules());
	etag('div',
	$grid->render()
	);
}

function module_action($module_name, $action)
{
	if (($module = CM5_Core::getInstance()->getModule($module_name)) === null)
		throw new Exception404();

	if (($action = $module->getAction($action)) === null)
		throw new Exception404();

	Layout::getActive()->getDocument()->title = CM5_Config::getInstance()->site->title .
        " | Module: {$module->getMetaInfoEntry('title')} > {$action['display']}";
	Layout::getActive()->getSubmenu()->create_entry($module->getMetaInfoEntry('title'));
	foreach($module->getActions() as $a)
	{
		CM5_Layout_Admin::getInstance()->getSubmenu()->create_link(
		$a['display'],
            ''
            )->set_link(UrlFactory::craft('module.action', $module->getMetaInfoEntry('nickname'), $a['name']), true);
	}

	etag('div class="contents"')->push_parent();
	call_user_func($action['callback']);
	Output_HTMLTag::pop_parent();

}

function module_configure($module_name)
{
	if (($module = CM5_Core::getInstance()->getModule($module_name)) === null)
		throw new Exception404();

	Layout::getActive()->getDocument()->title = CM5_Config::getInstance()->site->title .
        " | Module: {$module->getMetaInfoEntry('title')} > Configure";

	$frm = new CM5_Form_ModuleConfigure($module);
	etag('div',  $frm->render());
}

function module_enable($module_name)
{
	if (($module = CM5_Core::getInstance()->getModule($module_name)) === null)
		throw new Exception404();

	Layout::getActive()->getDocument()->title = CM5_Config::getInstance()->site->title .
        " | Module: {$module->getMetaInfoEntry('title')} > Enable";

	$frm = new CM5_Form_Confirm(
        'Module: ' . $module->getMetaInfoEntry('title'),
        'Are you sure you want to enable this module?',
        'Enable',
		function($m) {
			CM5_Core::getInstance()->enableModule($m->getConfigNickname());
			UrlFactory::craft("module.admin")->redirect();
		},
		array($module),
		UrlFactory::craft("module.admin")
	);
	etag('div',  $frm->render());
}

function module_disable($module_name)
{
	if (($module = CM5_Core::getInstance()->getModule($module_name)) === null)
		throw new Exception404();

	Layout::getActive()->getDocument()->title = CM5_Config::getInstance()->site->title .
        " | Module: {$module->getMetaInfoEntry('title')} > Disable";

	$frm = new CM5_Form_Confirm(
        'Module: ' . $module->getMetaInfoEntry('title'),
        'Are you sure you want to disable this module?',
        'Disable',
		function($m){
			CM5_Core::getInstance()->disableModule($m->getConfigNickname());
			UrlFactory::craft("module.admin")->redirect();
		},
		array($module),
		UrlFactory::craft("module.admin")
	);
	etag('div',  $frm->render());
}
