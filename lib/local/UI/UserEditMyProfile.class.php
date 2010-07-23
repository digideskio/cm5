<?php

class UI_UserEditMyProfile extends Output_HTML_Form
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

        parent::__construct(array(
            'old-password' => array('display' => 'Current password', 'type' => 'password'),
			'password' => array('display' => 'New password', 'type' => 'password',
			    'onerror' => 'Password must be at least 3 characters long.'),
			'password2' => array('display' => ' ', 'type' => 'password')
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
        $currentpass = $this->get_field_value('old-password');
        if (!Authn_Realm::get_backend()->authenticate($this->user->username, $currentpass))
        {
            $this->invalidate_field('old-password', 'You gave wrong password');
        }
        $pass1 = $this->get_field_value('password');
        $pass2 = $this->get_field_value('password2');
        
        if ((empty($pass1)) || (empty($pass2)) ||
            ($pass1 != $pass2))
                $this->invalidate_field('password2', 'You must write two times the same NEW password.');
    }

    public function on_valid($values)
    {
        if (!empty($values['password']))
            $this->user->password = sha1($values['password']);
        $this->user->save();
        UrlFactory::craft('user.admin')->redirect();
    }
};

?>
