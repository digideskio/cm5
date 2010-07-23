<?php

Stupid::add_rule('user_myprofile',
    array('type' => 'url_path', 'chunk[3]' => '/^\+myprofile$/')
);
Stupid::add_rule('edit_user',
    array('type' => 'url_path', 'chunk[3]' => '/^([\w]+)$/', 'chunk[4]' => '/^\+edit$/')
);
Stupid::add_rule('delete_user',
    array('type' => 'url_path', 'chunk[3]' => '/^([\w]+)$/', 'chunk[4]' => '/^\+delete$/')
);
Stupid::add_rule('create_user',
    array('type' => 'url_path', 'chunk[3]' => '/^\+create$/')
);

Stupid::set_default_action('show_users');
Stupid::chain_reaction();


function user_myprofile()
{
    $user = User::open(Authn_Realm::get_identity()->id());
        
    Layout::open('admin')->activate();

    $frm = new UI_UserEditMyProfile($user);
    etag('div', $frm->render());
}

function edit_user($username)
{
    if (!($u = User::open($username)))
        not_found();
        
    Layout::open('admin')->activate();

    $frm = new UI_UserEdit($u);
    etag('div', $frm->render());
}

function delete_user($username)
{
    if (!($u = User::open($username)))
        not_found();
        
    Layout::open('admin')->activate();
    if ($username == Authn_Realm::get_identity()->id())
    {
        etag('h2 class="error"', 'You cannot delete your self, login with another user before deleting this user.');
        etag('a', 'Back', array('href' => UrlFactory::craft('user.admin')));
        exit;
    }
    
    $frm = new UI_UserDelete($u);
    etag('div', $frm->render());
}

function create_user()
{
    Layout::open('admin')->activate();
    
    $frm = new UI_UserCreate();
    etag('div', $frm->render());
}

function show_users()
{
    Layout::open('admin')->activate();

    $grid = new UI_UsersGrid(User::open_all());
    etag('div',
        $grid->render(),
        UrlFactory::craft('user.create')->anchor('Create user')
            ->add_class('button')
            ->add_class('add')
    );
}
?>
