<?php

// Deploy checks
if (Config::get('site.deploy_checks'))
{
    if (is_writable(dirname(__FILE__) . '/config.inc.php'))
    {
        echo 'Security check: "config.inc.php" is writable, change file permissions and retry.';
        exit;
    }
    
    if (is_dir(dirname(__FILE__) . '/install'))
    {
        echo 'Security check: You must delete folder "/install" if you have installed site.';
        exit;
    }
}

// Initialize authentication for admin
$auth = new Authn_Backend_DB(array(
    'query_user' => User::open_query()
        ->where('enabled = ?')->push_exec_param(1)
        ->where('username = ?'),
    'field_username' => 'username',
    'field_password' => 'password',
    'hash_function' => 'sha1'
));
Authn_Realm::set_backend($auth);
Authn_Realm::set_session(
    new Authn_Session_Cache(
        new Cache_File(Config::get('site.cache_folder'), 'session_'),
        new Net_HTTP_Cookie('cms-session', null)
    )
);

// Special handling for special urls
Stupid::add_rule(create_function('', 'require(dirname(__FILE__) . \'/../login.php\');'),
    array('type' => 'url_path', 'chunk[-1]' => '/\+login/')
);
Stupid::add_rule(create_function('', 'require(dirname(__FILE__) . \'/../login.php\');'),
    array('type' => 'url_path', 'chunk[-1]' => '/\+logout/')
);

Stupid::add_rule(create_function('', "Net_HTTP_Response::redirect(url(\$_SERVER['PATH_INFO'] . '/+login'));"),
    array('type' => 'authn', 'op' => 'isanon')
);

Stupid::add_rule(function(){    require_once(dirname(__FILE__) . '/admin/files.php');    },
    array('type' => 'url_path', 'chunk[2]' => '/^files?$/')
);
Stupid::add_rule(function(){    require_once(dirname(__FILE__) . '/admin/pages.php');    },
    array('type' => 'url_path', 'chunk[2]' => '/^pages?$/')
);
Stupid::add_rule(function(){    require_once(dirname(__FILE__) . '/admin/modules.php');    },
    array('type' => 'url_path', 'chunk[2]' => '/^modules?$/')
);
Stupid::add_rule(function(){    require_once(dirname(__FILE__) . '/admin/users.php');    },
    array('type' => 'url_path', 'chunk[2]' => '/^users?$/')
);
Stupid::add_rule('tool_translit',
    array('type' => 'url_path', 'chunk[2]' => '/tools/', 'chunk[3]' => '/transliterate/'),
    array('type' => 'url_params', 'op' => 'isset', 'param' => 'text', 'param_type' => 'both')
);
Stupid::set_default_action('default_admin_panel');
Stupid::chain_reaction();

function default_admin_panel()
{
    UrlFactory::craft('page.admin')->redirect();
}

function tool_translit()
{
    $str = Net_HTTP_RequestParam::get('text');
	echo transliterate($str);;
}
?>
