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

require_once dirname(__FILE__) . '/lib/vendor/phplibs/ClassLoader.class.php';
require_once dirname(__FILE__) . '/lib/tools.lib.php';

/**
 * Here you can write code that will be executed at the begining of each page instance
 */

// Autoloader for local and phplibs classes
$phplibs_loader = new ClassLoader(
    array(
    dirname(__FILE__) . '/lib/vendor/phplibs',
    dirname(__FILE__) . '/lib/vendor',
    dirname(__FILE__) . '/lib/local'
));
$phplibs_loader->set_file_extension('.class.php');
$phplibs_loader->register();

// Zend
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/lib/vendor');
$zend_loader = new ClassLoader(array(dirname(__FILE__) . '/lib/vendor'));
$zend_loader->register();

// Load static library for HTML
require_once dirname(__FILE__) . '/lib/vendor/phplibs/Output/html.lib.php';

// Load urls library
require_once dirname(__FILE__) . '/lib/urls.lib.php';

// Load configuration file
GConfig::$default_config = array(
    'module' => array(),
    'enabled_modules' => '',
);
GConfig::$config_file = dirname(__FILE__) . '/config.inc.php';
GConfig::load_config();
$config = GConfig::get_instance();

// Database connection
DB_Conn::connect($config->db->host, $config->db->user, $config->db->pass, $config->db->schema, true);
DB_Conn::query('SET NAMES utf8;');
DB_Conn::query("SET time_zone='+0:00';");
DB_Conn::events()->connect('error',
    create_function('$e', ' error_log( $e->arguments["message"]); 
    CM5_Logger::get_instance()->crit($e->arguments["message"]);'));
//DB_Conn::events()->connect('stmt.executed',
//    create_function('$e', ' error_log( $e->arguments[0]); '));

// PHP TimeZone
date_default_timezone_set($config->site->timezone);

// Initialize CMS
$cache_engine = new Cache_File($config->site->cache_folder, 'pages_');
CM5_Core::init($cache_engine);

?>
