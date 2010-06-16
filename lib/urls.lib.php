<?php

UrlFactory::register('page.view', '$page', '/{$page->full_path()}');
UrlFactory::register('page.edit', '$page', '/admin/page/{$page->id}');
UrlFactory::register('page.create', '$page_id', '/admin/page/+create?parent={$page_id}');
UrlFactory::register('upload.admin', '', '/admin/files');
UrlFactory::register('upload.create', '', '/admin/files/+upload');
UrlFactory::register('upload.view', '$f_id', '/file/{$f_id}');
UrlFactory::register('upload.thumb', '$f_id', '/file/{$f_id}/+thumb');
UrlFactory::register('upload.edit', '$f_id', '/admin/files/{$f_id}/+edit');
UrlFactory::register('upload.delete', '$f_id', '/admin/files/{$f_id}/+delete');
?>
