#!/usr/bin/env php
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


/**
 * Folders and files that must be upgrades
 * @var array
 */
$for_upgrade = array(
	'lib',
	'modules',
	'static',
	'themes',
	'web',
	'bootstrap.php',
	'index.php', 
	'install',
	'LICENCE.txt'
);

/**
 * Show console usage information.
 */
function show_usage()
{
	$args = $_SERVER['argv'];
	$executable = basename($args[0]);

echo <<< EOF
Usage: "{$executable}" -t= [--go]
Upgrades an existing CM5 instaltion to _this_ version and _this_ refers
to the version where "{$executable}" is located at.
 
   -t:  Path where there is an existing installation of CM5.
 --go:  Actually execute the upgrade. (Default is simulating upgrade)

NOTICE: Allthough "{$executable}" will not touch any user configuration
or database data, it is highly advised that you first _backup_ your
existing installation.

EOF;
}

/**
 * 
 * @see http://www.php.net/manual/en/function.copy.php#91256
 * Copy file or folder from source to destination, it can do
 * recursive copy as well and is very smart
 * It recursively creates the dest file or directory path if there weren't exists
 * Situtaions :
 * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
 * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
 * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest
 * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
 * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
 * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
 * @todo
 *     - Should have rollback technique so it can undo the copy when it wasn't successful
 *  - Auto destination technique should be possible to turn off
 *  - Supporting callback function
 *  - May prevent some issues on shared enviroments : http://us3.php.net/umask
 * @param $source //file or folder
 * @param $dest ///file or folder
 * @param $options //folderPermission,filePermission
 * @return boolean
 */
function smartCopy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
{
	$result=false;

	if (is_file($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if (!file_exists($dest)) {
				cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
			}
			$__dest=$dest."/".basename($source);
		} else {
			$__dest=$dest;
		}
		$result=copy($source, $__dest);
		chmod($__dest,$options['filePermission']);
		 
	} elseif(is_dir($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if ($source[strlen($source)-1]=='/') {
				//Copy only contents
			} else {
				//Change parent itself and its contents
				$dest=$dest.basename($source);
				@mkdir($dest);
				chmod($dest,$options['filePermission']);
			}
		} else {
			if ($source[strlen($source)-1]=='/') {
				//Copy parent directory with new name and all its content
				@mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
			} else {
				//Copy parent directory with new name and all its content
				@mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
			}
		}

		$dirHandle=opendir($source);
		while($file=readdir($dirHandle))
		{
			if($file!="." && $file!="..")
			{
				if(!is_dir($source."/".$file)) {
					$__dest=$dest."/".$file;
				} else {
					$__dest=$dest."/".$file;
				}
				//echo "$source/$file ||| $__dest<br />";
				$result=smartCopy($source."/".$file, $__dest, $options);
			}
		}
		closedir($dirHandle);
		 
	} else {
		$result=false;
	}
	return $result;
}

/**
 * Check if a folder is a CM5 package/installation
 * @param string $basedir The path to folder that must be checked.
 * @return boolean True if it was or false if not.
 */
function is_cm5_directory($basedir)
{
	if (!is_dir($basedir))
		return false;
	
	$structure = array(		
		'lib' => 'dir',
		'modules' => 'dir',
		'static' => 'dir',
		'themes' => 'dir',
		'uploads' => 'dir',
		'web' => 'dir',
		'bootstrap.php' => 'file',
		'config.inc.php' => 'file',
		'index.php' => 'file'
	);
	
	foreach($structure as $entry => $type) {
		if ($type == 'dir')
			if (!is_dir($basedir . '/' . $entry))
				return false;
		elseif ($type == 'file')
			if (!is_file($basedir . '/' . $entry))
				return false;
	}
	return true;
}

/**
 * Analayze a CM5 folder and determine its version.
 * @param string $basedir The path to folder that must be checked.
 * @return array With all parts of version (major, minor, rev)
 */
function get_cm5_version($basedir)
{
	$core_file = $basedir . "/lib/local/CM5/Core.class.php";
	
	$res = preg_match('#\$version[\s]*=[\s]*array\(([\s]*([\d]+)[\s]*,[\s]*)([\s]*([\d]+)[\s]*,[\s]*)([\s]*([\d]+)[\s]*[\s]*)[\s]*\)#', file_get_contents($core_file), $matches);
	if (!count($res))
		return false;

	return array($matches[2],$matches[4],$matches[6]);
}

/**
 * Format a version to string
 * @param array $version The version to convert
 * @return string The string representation of version
 */
function version_to_str($version)
{
	return "$version[0].$version[1].$version[2]";
}

/**
 * Compares two version and returns if left is bigger than right.
 * @param array $lvalue Array with version
 * @param array $rvalue Array with version
 * @return integer
 *  - \> 0 : if $lvalue > $rvalue 
 *  - = 0 : if $lvalue == $rvalue
 *  - \< 0 : if $lvalue < $rvalue
 *  .
 */
function compare_version($lvalue, $rvalue)
{
	if ($lvalue[0] != $rvalue[0])
		return $lvalue[0] > $rvalue[0]? 1: -1;
	
	if ($lvalue[1] != $rvalue[1])
		return $lvalue[1] > $rvalue[1]? 1: -1;
	
	if ($lvalue[2] != $rvalue[2])
		return $lvalue[2] > $rvalue[2]? 1: -1;
	return 0;	// equal
}

/**
 * Check if upgrade is supported
 * @param array $from_version The initial version.
 * @param array $to_version The target version.
 */
function is_upgrade_supported($from_version, $to_version)
{
	if (compare_version($to_version, $from_version) <= 0)
		return false; // Cannot do downgrade
		
	// Major upgrades are not supported by default
	if ($from_version[0] != $to_version[0])
		return false;
	return true;
}

/*------------------------------------------------------------------------------------------
 * Program ENTRY POINT
 */

/**
 * Commandline arguments as they where analyzed from getopt
 * @var array
 */
$options = getopt("t:", array("go"));

// Argument checks
if ((!$options) || (!isset($options['t']))) {
	show_usage();
	exit;	
}

/**
 * Flag if the process must be simulated or actually executed
 * @var boolean
 */
$simulated = isset($options['go'])?false:true;

/**
 * The name of this file
 * @var string
 */
$executable = basename($_SERVER['argv'][0]);

/**
 * Target CM5 installation folder
 * @var string 
 */
$target = realpath($options['t']);
if (! is_cm5_directory($target))
	die("This is not valid CM5 installation.\n");

/**
 * Target CM5 installation version
 * @var array
 */
$target_version = get_cm5_version($target);

/**
 * Source CM5 package folder
 * @var string 
 */
$source = realpath(dirname(__FILE__) . '/../');
if (! is_cm5_directory($target))
	die("{$executable} must exists inside /install folder of source directory.\n");

/**
 * Source CM5 package version
 * @var array
 */
$source_version = get_cm5_version($source);

// Compare target source directories
if ($target == $source )
	die("You cannot upgrade the source. Source and target must be different.\n");

// Compare target source version
if (compare_version($target_version, $source_version) >= 0 )
	die("You can only upgrade to a newer version.\n");

/*--- DATA IS OK, LETS WORK -- */
	
// Output Header 
echo "\nCM5 \033[31;1m↑\033[0mupgrade\033[31;1m↑\033[0m " . ($simulated?'(simulated)':'') . "\n";
echo "Version: [" . version_to_str($target_version) . "] => [" . version_to_str($source_version) . "] ";
if (is_upgrade_supported($target_version, $source_version)) {
	echo "\033[32;1msupported\033[0m.\n";
} else {
	echo "\033[31;1mNOT SUPPORTED\033[0m.\n";
	exit;
}
echo "Target: {$target}\n";
echo "\n";

// Process folders
$errors = false;
foreach($for_upgrade as $upgradable) {
	$t_upgradable = $target . "/{$upgradable}";
	$s_upgradable = $source . "/{$upgradable}";
	
	echo "[\033[32;1m{$upgradable}\033[0m]";	
	
	
	// Skip new entries
	if (!file_exists($target . '/' . $upgradable)) {
		echo " \033[35mskip\033[0m as it does not exist at target.\n";
		continue;
	}
	// Check file type
	else if (filetype($t_upgradable) != filetype($s_upgradable)) {
		echo " \033[31;1merror\033[0m entries are of different type.\n ";
		$errors = true;
		continue;
	}
		
	// Print information
	echo " \033[35;1movewrite\033[0m";
	if (is_file($t_upgradable))
		echo " destination file... ";
	else
		echo " recursively destination folder... ";
		
	// Do the action
	if ($simulated) {
		echo "\033[32;1msimulated\033[0m\n";
	} else {
		if (! ($res = smartCopy($s_upgradable, $t_upgradable))) {
			echo "\033[31;1merror\033[0m\n";
			$errors = true;
			break;
		} else {
			echo "\033[32;1mdone\033[0m\n";
		}			
	}
}

if (!$simulated) {
	
	if ($errors)
		echo "\033[31;1mThere were some errors at upgrade. The result is unknown...\033[0m\n";
	else {
		echo "Upgrade process finished with no errors!\n";
		echo "Login at the admin interface and resave a page to reset cache.\n";
	}
}
