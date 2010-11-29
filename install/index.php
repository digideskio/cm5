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

require_once dirname(__FILE__) . '/../lib/vendor/phplibs/ClassLoader.class.php';
require_once dirname(__FILE__) . '/../lib/tools.lib.php';

// Autoloader for local and phplibs classes
$phplibs_loader = new ClassLoader(
    array(
    dirname(__FILE__) . '/../lib/vendor/phplibs',
    dirname(__FILE__) . '/../lib/local'
));
$phplibs_loader->set_file_extension('.class.php');
$phplibs_loader->register();

// Zend
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../lib/vendor');
$zend_loader = new ClassLoader(array(dirname(__FILE__) . '/../lib/vendor'));
$zend_loader->register();

// Load static library for HTML
require_once dirname(__FILE__) . '/../lib/vendor/phplibs/Output/html.lib.php';

// File names
$fn_config = dirname(__FILE__) . '/../config.inc.php';
$fn_htaccess = dirname(__FILE__) . '/../.htaccess';


$dl = new Layout('debug');
$dl->activate();
$dl->get_document()->add_ref_css(surl('/../static/debug/debug.css'));
$dl->get_document()->title = 'Installation';

etag('h2', 'PHPLibs Skeleton');
etag('h3', 'Installation process');

// Make checks for writable files
if (! is_writable($fn_config))
{
    etag('div class="error" nl_escape_on', 'Cannot continue installing site.
        The configuration file "config.inc.php" must be writable, you can change
        permissions and retry installation.');
    exit;
}

if (! is_writable(dirname(__FILE__) . '/../uploads'))
{
    etag('div class="error" nl_escape_on', 'Cannot continue installing site.
        The uploads folder "/uploads" must be writable, you can change
        permissions and retry installation.');
    exit;
}

if (! is_writable(dirname(__FILE__) . '/../cache'))
{
    etag('div class="error" nl_escape_on', 'Cannot continue installing site.
        The thumbnails cache folder "/cache" must be writable, you can change
        permissions and retry installation.');
    exit;
}

$f = new UI_InstallationForm($fn_config, dirname(__FILE__) . '/build-script.php');
etag('div', $f->render());
?>
