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

require_once __DIR__ . '/../authnz.php';

// Load modules
CM5_Core::getInstance()->switchBackendWorkingContext();
CM5_Core::getInstance()->loadModules();
CM5_Core::getInstance()->loadThemes();
CM5_Core::getInstance()->getSelectedTheme()->initialize('backend');

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
