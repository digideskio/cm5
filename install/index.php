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

require_once __DIR__ . '/../lib/vendor/phplibs/ClassLoader.class.php';
require_once __DIR__ . '/../lib/tools.lib.php';

// Autoloader for local and phplibs classes
$phplibs_loader = new ClassLoader(
    array(
    __DIR__ . '/../lib/vendor/phplibs',
    __DIR__ . '/../lib/local'
));
$phplibs_loader->set_file_extension('.class.php');
$phplibs_loader->register();

// Zend
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../lib/vendor');
$zend_loader = new ClassLoader(array(__DIR__ . '/../lib/vendor'));
$zend_loader->register();

// Load static library for HTML
require_once __DIR__ . '/../lib/vendor/phplibs/Output/html.lib.php';

// File names
$fn_config = __DIR__ . '/../config.inc.php';
$fn_htaccess = __DIR__ . '/../.htaccess';


$dl = new Layout('debug');
$dl->activate();
$dl->getDocument()->add_ref_css(surl('/../static/debug/debug.css'));
$dl->getDocument()->title = 'Installation';

etag('h2', 'CM5');
etag('h3', 'Installation process');

// Make checks for writable files
if (! is_writable($fn_config))
{
    etag('div class="error" nl_escape_on', 'Cannot continue installing site.
        The configuration file "config.inc.php" must be writable, you can change
        permissions and retry installation.');
    exit;
}

if (! is_writable(__DIR__ . '/../uploads'))
{
    etag('div class="error" nl_escape_on', 'Cannot continue installing site.
        The uploads folder "/uploads" must be writable, you can change
        permissions and retry installation.');
    exit;
}

if (! is_writable(__DIR__ . '/../cache'))
{
    etag('div class="error" nl_escape_on', 'Cannot continue installing site.
        The thumbnails cache folder "/cache" must be writable, you can change
        permissions and retry installation.');
    exit;
}

$f = new UI_InstallationForm($fn_config, __DIR__ . '/build-script.php');
if ($f->process() == Form::RESULT_VALID){
        
        etag('strong', 'Installation finished succesfully !');
        etag('p class="error"', 'For security reasons you must delete folder "install" from web server.');
        
        $relative_folder = implode('/', array_slice(explode('/', dirname($_SERVER['SCRIPT_NAME'])), 0, -1));        
        if (!empty($relative_folder))
            etag('p class="error"', 'Site is running under a subdirectory, for proper support of ' .
                'cool urls, the .htaccess file must be edit and the option ', tag('strong', 'RewriteBase'),
                ' should be change to: ',
            tag('pre class="code"', "RewriteBase $relative_folder")
            );
} else
	etag('div', $f->render());

