<?php
return array (
  'module' => 
  array (
    'google-analytics' => 
    array (
      'inform_admin' => false,
      'property_id' => 'kkoka',
    ),
  ),
  'enabled_modules' => 'seo,content-magic,migration,google-analytics,',
  'db' => 
  array (
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'root',
    'schema' => 'cms',
    'prefix' => '0x0lab_',
  ),
  'site' => 
  array (
    'title' => '0x0lab',
    'timezone' => 'Europe/Athens',
    'deploy_checks' => false,
    'theme' => 'antispe',
    'cache_folder' => '/home/sque/vcs/cms/cache',
    'upload_folder' => '/home/sque/vcs/cms/uploads',
  ),
  'email' => 
  array (
    'sender' => 'sque@localhost',
    'administrator' => 'sque@localhost',
  ),
);
