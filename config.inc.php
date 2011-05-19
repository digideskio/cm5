<?php
return array (
  'module' => 
  array (
    'youtube' => 
    array (
      'video-width' => '425',
      'video-height' => '344',
      'privacy-enchanced' => NULL,
      'border' => NULL,
      'controls-color-1' => '#eb3b3b',
      'controls-color-2' => '#d2d2d2',
      'use-iframe' => true,
    ),
    'default' => 
    array (
      'page-background-color' => '#4B484F',
      'article-background-color' => '#F6F5FF',
      'article-text-color' => '#d91414',
      'menu-background-color' => '#F0F0F0',
      'menu-text-color' => '#cc3131',
      'menu-selected-background-color' => '#423b2b',
      'menu-selected-text-color' => '#FFFFFF',
      'footer' => 'using <a target="_blank" href="http://code.0x0lab.org/p/cm5">CM5</a>',
      'favicon-url' => '',
      'extra-css' => '',
    ),
    'google-analytics' => 
    array (
      'inform_admin' => false,
      'property_id' => 'test',
    ),
    '0x0lab' => 
    array (
      'footer' => 'using <a target="_blank" href="http://code.0x0lab.org/p/cm5">CM5</a>',
      'favicon-url' => '',
      'extra-css' => '',
    ),
  ),
  'enabled_modules' => 'revisions,content-magic,migration,youtube,',
  'db' => 
  array (
    'host' => 'localhost',
    'schema' => 'cm5',
    'user' => 'root',
    'pass' => 'root',
    'prefix' => '',
    'build' => NULL,
  ),
  'site' => 
  array (
    'upload_folder' => '/home/sque/Projects/cm5/uploads',
    'cache_folder' => '/home/sque/Projects/cm5/cache',
    'theme' => '0x0lab',
    'title' => 'Test',
    'timezone' => 'Europe/Athens',
  ),
  'email' => 
  array (
    'administrator' => 'sque@localhost',
    'sender' => 'www-data@localhost',
  ),
);
