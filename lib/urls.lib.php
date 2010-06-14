<?php

UrlFactory::register('page.view', '$page', '/{$page->full_path()}');
UrlFactory::register('page.edit', '$page', '/admin/page/{$page->id}');
UrlFactory::register('page.create', '$page_id', '/admin/page/+create?parent={$page_id}');
?>
