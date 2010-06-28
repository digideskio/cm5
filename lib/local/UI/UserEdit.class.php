<?php

class UI_UserEdit extends Output_HTML_Form
{
    public function __construct($u)
    {
        $this->user = $u;
        
        $groups = array();
        foreach(Group::open_all() as $g)
            $groups[$g->groupname] = $g->groupname;
        
        $groupselected = array();
        foreach($u->groups->all() as $g)
            $groupselected[$g->groupname] = true;
           
        var_dump($groupselected);
        parent::__construct(array(
			'password' => array('display' => 'Reset password', 'type' => 'password',
			    'onerror' => 'Password must be at least 3 characters long.'),
			'password2' => array('display' => ' ', 'type' => 'password'),
			'enabled' => array('display' => 'Enabled', 'type' => 'checkbox', 'value' => $u->enabled),
			'groups' => array('display' => 'Groups', 'value' => $groupselected,
			    'type' => 'checklist', 'optionlist' => $groups)
        ),
        array('title' => 'Edit user "' . $u->username . '"',
            'css' => array('ui-form'),
		    'buttons' => array(
		        'save' => array('display' => 'Save'),
		        'cancel' => array('display' => 'Cancel', 'type' => 'button',
		            'onclick' => "window.location='" . (string)UrlFactory::craft('user.admin') . "'")
                )
            )
        );
    }
    
    public function on_post()
    {
        $pass1 = $this->get_field_value('password');
        $pass2 = $this->get_field_value('password2');
        
        if ((!empty($pass1)) && (!empty($pass2)))
            if ($pass1 != $pass2)
                $this->invalidate_field('password2', 'Passwords do not match.');
    }

    public function on_valid($values)
    {
        $this->user->enabled = $values['enabled'];
        if (!empty($values['password']))
            $this->user->password = $values['password'];
        $this->user->save();
        Membership::raw_query()
            ->delete()
            ->where('username = ?')
            ->execute($this->user->username);

        // Create memberships
        foreach($values['groups'] as $group => $enabled)
        {
            if ($enabled)
                Membership::create(array(
                    'username' => $this->user->username,
                    'groupname' => $group
                ));
        }
        
        UrlFactory::craft('user.admin')->redirect();
    }
};

?>
