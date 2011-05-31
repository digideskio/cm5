<?php 

class Install_Layout extends Layout {
	
	
	public function onInitialize() {
		$version = array('0', '11', '1');
		$this->getDocument()->add_ref_css(surl('/../static/css/admin.css'));
		$this->getDocument()->add_ref_css(surl('/../static/debug/install.css'));
		$this->getDocument()->title = 'CM5 - Installation';

		$this->activateSlot();
        etag('div id="wrapper"')->push_parent();
        etag('div id="header"',
            tag('h1', 
                tag('a target="_blank"', 'CM5 Installation', tag('span', " v{$version[0]}.{$version[1]}.{$version[2]}"))),
            $loginfo = tag('div id="login-info"'),
            tag('div id="main-menu"', ' ')
        );
        etag('div id="main"',
            $def_content = 
            tag('div id="content"')
        );
        
        //$version = CM5_Core::getInstance()->getVersion();
        
        etag('div id="footer"', 
            tag('ul',
                tag('li',
                    tag('a', "CM5 v{$version[0]}.{$version[1]}.{$version[2]}",
                        array('href' => 'http://code.0x0lab.org/p/cm5', 'target' => '_blank'))
                ),
                tag('li',
                    'made with ', tag('a', 'PHPlibs',
                        array('href' => 'http://phplibs.kmfa.net', 'target' => '_blank'))
                )
            )
        );
        
        $this->setSlot('default', $def_content);
	}
}