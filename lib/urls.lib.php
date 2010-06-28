<?php

UrlFactory::register('page.view', '$page', '{$page->uri}');
UrlFactory::register('page.edit', '$page_id', '/admin/page/{$page_id}');
UrlFactory::register('page.delete', '$page_id', '/admin/page/{$page_id}/+delete');
UrlFactory::register('page.create', '$page_id', '/admin/page/+create?parent={$page_id}');
UrlFactory::register('page.admin', '', '/admin/page');
UrlFactory::register('upload.admin', '', '/admin/files');
UrlFactory::register('upload.create', '', '/admin/files/+upload');
UrlFactory::register('upload.view', '$f_id', '/file/{$f_id}');
UrlFactory::register('upload.thumb', '$f_id', '/file/{$f_id}/+thumb');
UrlFactory::register('upload.edit', '$f_id', '/admin/files/{$f_id}/+edit');
UrlFactory::register('upload.delete', '$f_id', '/admin/files/{$f_id}/+delete');
UrlFactory::register('user.edit', '$u', '/admin/user/{$u}/+edit');
UrlFactory::register('user.delete', '$u', '/admin/user/{$u}/+delete');
UrlFactory::register('user.create', '', '/admin/user/+create');
UrlFactory::register('user.admin', '', '/admin/users');
?>
