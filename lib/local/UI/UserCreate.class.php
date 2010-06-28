<?php

class UI_UserCreate extends Output_HTML_Form
{
    public function __construct()
    {
        $groups = array();
        foreach(Group::open_all() as $g)
            $groups[$g->groupname] = $g->groupname;

        parent::__construct(array(
			'username' => array('display' => 'Username', 'regcheck' => '/^[a-z0-9_\-]+$/',
			    'onerror' => 'Username can have lower case letters, numbers, dash and underscore.'),
			'password' => array('display' => 'Password', 'regcheck' => '/^.{3,}$/', 'type' => 'password',
			    'onerror' => 'Password must be at least 3 characters long.'),
			'password2' => array('display' => ' ', 'type' => 'password'),
			'groups' => array('display' => 'Groups', 
			    'type' => 'checklist', 'optionlist' => $groups)
        ),
        array('title' => 'Create new user',
            'css' => array('ui-form'),
		    'buttons' => array(
		        'create' => array('display' => 'Create')
                )
            )
        );
    }
    
    public function on_post()
    {
        if ($this->get_field_value('password') != $this->get_field_value('password2'))
            $this->invalidate_field('password2', 'Passwords do not match.');
    }

    public function on_valid($values)
    {

        $u = User::create(array(
            'username' => $values['username'],
            'password' => sha1($values['password']),
            'enabled' => true
        ));
        
        if (!$u)
        {
            $this->invalidate_field('username', 'There was an error creating user.');
            return;
        }

        // Create memberships
        foreach($values['groups'] as $group => $enabled)
        {
            if ($enabled)
                Membership::create(array(
                    'username' => $values['username'],
                    'groupname' => $group
                ));
        }
        
        UrlFactory::craft('user.admin')->redirect();
    }
};

?>
