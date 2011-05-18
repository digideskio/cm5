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

// Deploy checks
if (CM5_Config::getInstance()->site->deploy_checks)
{
    if (is_writable(__DIR__ . '/config.inc.php'))
    {
        echo 'Security check: "config.inc.php" is writable, change file permissions and retry.';
        exit;
    }
    
    if (is_dir(__DIR__ . '/install'))
    {
        echo 'Security check: You must delete folder "/install" if you have installed site.';
        exit;
    }
}

// Initialize authentication for admin
$auth = new Authn_Backend_DB(array(
    'query_user' => CM5_Model_User::open_query()
        ->where('enabled = ?')->push_exec_param(1)
        ->where('username = ?'),
    'field_username' => 'username',
    'field_password' => 'password',
    'hash_function' => 'sha1'
));
Authn_Realm::set_backend($auth);
Authn_Realm::set_session(
    new Authn_Session_Cache(
        new Cache_File(CM5_Config::getInstance()->site->cache_folder, 'session_'),
        new Net_HTTP_Cookie('cms-session', null, time()+(86400 * 15), surl('/'))
    )
);

// Initialize authorization
$roles = new Authz_Role_FeederDatabase(array(
    'role_query' => CM5_Model_User::open_query()->where('username = ?'),
    'role_name_field' => 'username',
    'parents_query' => CM5_Model_Membership::open_query()->where('username = ?'),
    'parent_name_field' => 'groupname',
    'parent_name_filter_func' => function($name){ return "@" . $name; }
));
Authz::set_resource_list($list = new Authz_ResourceList());
Authz::set_role_feeder($roles);
$list->add_resource('page');
$list->add_resource('user');
$list->add_resource('file');
$list->add_resource('module');
$list->add_resource('theme');
$list->add_resource('log');
$list->add_resource('system.settings');

Authz::allow('page', '@editor', 'admin');
Authz::allow('page', null, 'view');
Authz::allow('page', '@editor', 'create');
Authz::allow('page', '@editor', 'edit');
Authz::allow('page', '@editor', 'delete');

Authz::allow('file', '@editor', 'admin');
Authz::allow('file', null, 'view');
Authz::allow('file', '@editor', 'create');
Authz::allow('file', '@admin', 'edit');
Authz::allow('file', '@admin', 'delete');

Authz::allow('module', '@admin', 'admin');
Authz::allow('module', '@admin', 'view');
Authz::allow('module', '@admin', 'change-status');
Authz::allow('module', '@admin', 'config');

Authz::allow('theme', '@admin', 'admin');

Authz::allow('user', '@admin', 'admin');
Authz::allow('user', '@admin', 'view');
Authz::allow('user', '@admin', 'edit');

Authz::allow('log', '@admin', 'view');

Authz::allow('system.settings', '@admin', 'admin');

// Load modules
CM5_Core::getInstance()->loadModules();

// Special handling for special urls
Stupid::add_rule(function() {require(__DIR__ . '/../login.php'); },
    array('type' => 'url_path', 'chunk[-1]' => '/\+login/')
);
Stupid::add_rule(function() {require(__DIR__ . '/../login.php'); },
    array('type' => 'url_path', 'chunk[-1]' => '/\+logout/')
);

Stupid::add_rule(function() { Net_HTTP_Response::redirect(url($_SERVER['PATH_INFO'] . '/+login')); },
    array('type' => 'authn', 'op' => 'isanon')
);

Stupid::add_rule(function() { require_once(__DIR__ . '/admin/files.php'); },
    array('type' => 'url_path', 'chunk[2]' => '/^files?$/'),
    array('type' => 'authz', 'resource' => 'file', 'action' => 'admin')
);
Stupid::add_rule(function() { require_once(__DIR__ . '/admin/editor.php'); },
    array('type' => 'url_path', 'chunk[2]' => '/^editor$/'),
    array('type' => 'authz', 'resource' => 'page', 'action' => 'admin')
);
Stupid::add_rule(function() { require_once(__DIR__ . '/admin/modules.php'); },
    array('type' => 'url_path', 'chunk[2]' => '/^modules?$/'),
    array('type' => 'authz', 'resource' => 'module', 'action' => 'admin')
);
Stupid::add_rule(function() { require_once(__DIR__ . '/admin/users.php'); },
    array('type' => 'url_path', 'chunk[2]' => '/^users?$/'),
    array('type' => 'authz', 'resource' => 'user', 'action' => 'admin')
);
Stupid::add_rule(function() { require_once(__DIR__ . '/admin/themes.php'); },
    array('type' => 'url_path', 'chunk[2]' => '/^themes?$/'),
    array('type' => 'authz', 'resource' => 'theme', 'action' => 'admin')
);

Stupid::add_rule(function() { require_once(__DIR__ . '/admin/log.php'); },
    array('type' => 'url_path', 'chunk[2]' => '/^log$/'),
    array('type' => 'authz', 'resource' => 'log', 'action' => 'view')
);

Stupid::add_rule(function() { require_once(__DIR__ . '/admin/settings.php'); },
    array('type' => 'url_path', 'chunk[2]' => '/^settings$/'),
    array('type' => 'authz', 'resource' => 'system.settings', 'action' => 'admin')
);

Stupid::add_rule(function(){
		CM5_Layout_Admin::getInstance()->activateSlot();
		$frm = new UI_TestForm(array());
		$frm->render()->appendTo(Output_HTMLTag::get_current_parent());
		exit;
	},
    array('type' => 'url_path', 'chunk[2]' => '/^test$/')
);

Stupid::add_rule(function() { require_once(__DIR__ . '/admin/tools.php'); },
    array('type' => 'url_path', 'chunk[2]' => '/^tools$/')
);

Stupid::set_default_action('default_admin_panel');

// Enable admin layout and start
CM5_Layout_Admin::getInstance()->activateSlot();
Stupid::chain_reaction();

function default_admin_panel()
{
    UrlFactory::craft('page.admin')->redirect();
}
