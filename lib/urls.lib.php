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

UrlFactory::register('page.view', '$page', '{$page->uri}');
UrlFactory::register('page.edit', '$page_id', '/admin/editor#{$page_id}');
UrlFactory::register('page.editform', '$page_id', '/admin/editor/{$page_id}/+form');
UrlFactory::register('page.delete', '$page_id', '/admin/editor/{$page_id}/+delete');
UrlFactory::register('page.create', '$page_id', '/admin/editor/+create?parent={$page_id}');
UrlFactory::register('page.admin', '', '/admin/editor');
UrlFactory::register('upload.admin', '', '/admin/files');
UrlFactory::register('upload.create', '', '/admin/files/+upload');
UrlFactory::register('upload.view', '$f', '/file/{$f->filename}');
UrlFactory::register('upload.thumb', '$f', '/file/+thumb/{$f->filename}');
UrlFactory::register('upload.edit', '$f_id', '/admin/files/{$f_id}/+edit');
UrlFactory::register('upload.delete', '$f_id', '/admin/files/{$f_id}/+delete');
UrlFactory::register('user.edit', '$u', '/admin/user/{$u}/+edit');
UrlFactory::register('user.delete', '$u', '/admin/user/{$u}/+delete');
UrlFactory::register('user.create', '', '/admin/user/+create');
UrlFactory::register('user.admin', '', '/admin/users');
UrlFactory::register('user.me', '', '/admin/user/+myprofile');
UrlFactory::register('theme.admin', '', '/admin/themes');
UrlFactory::register('theme.switch', '$module', '/admin/themes/{$module}/+switch');
UrlFactory::register('theme.config', '$module', '/admin/themes/{$module}/+configure');
UrlFactory::register('module.admin', '', '/admin/modules');
UrlFactory::register('module.action', '$module, $action', '/admin/modules/@{$module}/{$action}');
UrlFactory::register('module.config', '$module', '/admin/modules/{$module}/+configure');
UrlFactory::register('module.enable', '$module', '/admin/modules/{$module}/+enable');
UrlFactory::register('module.disable', '$module', '/admin/modules/{$module}/+disable');
UrlFactory::register('log.view', '', '/admin/log');
UrlFactory::register('log.view_filtered', '$priorites', '/admin/log?priorites={$priorites}');
UrlFactory::register('log.clear', '', '/admin/log/+clear');
UrlFactory::register('system.settings', '', '/admin/settings');
?>
