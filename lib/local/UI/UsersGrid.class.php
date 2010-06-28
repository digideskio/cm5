<?php

    class UI_UsersGrid extends Output_HTML_Grid
    {
        public function __construct($users)
        {
            $this->users = $users;
            parent::__construct(
                array(
                    'enabled' => array('caption' => 'Enabled', 'customdata' => 'true'),
                    'username' => array('caption' => 'Username'),
                    'groups' => array('caption' => 'Groups', 'customdata' => true),
                    'tools' => array('caption' => 'Tools', 'customdata' => 'true'),
                ),
                array(
                ), 
                $this->users
            );
        }
        
        public function on_custom_data($col_id, $row_id, $user)
        {
            if ($col_id == 'enabled')
            {
                $check = tag('input type="checkbox" disabled="disabled"');
                if ($user->enabled)
                    $check->attr('checked', 'true');
                return $check;
            }
            else if ($col_id == 'groups')
            {
                $groups = array();
                foreach($user->groups->all() as $g)
                    $groups[] = $g->groupname;

                return implode(', ', $groups);
            }
            else if ($col_id == 'tools')
            {
                return tag('ul class="actions"',
                    tag('li',
                        UrlFactory::craft('user.edit', $user->username)->anchor('Edit')->add_class('edit')),
                    tag('li',
                        UrlFactory::craft('user.delete',  $user->username)->anchor('Delete')->add_class('delete'))
                );
                return $res;
            }
        }
    }

?>
