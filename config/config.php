<?php
system\Config::set('site_name', 'Template • TodoMVC');

system\Config::set('routes', [
    'default' => '',
    'admin' => 'admin'
]);

system\Config::set('default_route', 'default');
system\Config::set('default_controller', 'engine');
system\Config::set('default_action', 'index');

system\Config::set('passwd_salt', 'ubdk3421GrD8');
system\Config::set('confirm_salt', 'gr6Abd9t3Kl1');

system\Config::set('db', [
   'default' => [
       'host' => 'localhost',
       'username' => 'todouser',
       'passwd' => '12345678',
       'db_name' => 'todolist',
       'charset' => 'utf8'
   ]
]);