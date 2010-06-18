<?php
// Show a web page
//CMS_Core::get_instance()->serve();
Layout::open('default')->activate();
$path = substr($_SERVER['PATH_INFO'], 1);

$p = Page::open_query()
    ->where('slug = ?')
    ->limit(1)
    ->execute($path);

if (!$p)
    not_found();

etag('h1', $p[0]->title);
etag('div html_escape_off', $p[0]->body);
?>
