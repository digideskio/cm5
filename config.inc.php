<?php
return array (
  'module' => 
  array (
    'google-analytics' => 
    array (
      'inform_admin' => false,
      'property_id' => 'UI-000000-0',
    ),
    'antispe' => 
    array (
      'bottom-column1' => '2',
      'bottom-column2' => '-1',
      'bottom-column3' => '9',
      'bottom-column4' => '-1',
      'bottom-column5' => '12',
    ),
  ),
  'enabled_modules' => 'migration,seo,content-magic,google-analytics,',
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
    'theme' => 'default',
    'cache_folder' => '/home/sque/vcs/cms/cache',
    'upload_folder' => '/home/sque/vcs/cms/uploads',
  ),
  'email' => 
  array (
    'sender' => 'sque@localhost',
    'administrator' => 'sque@localhost',
  ),
);
