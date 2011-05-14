<?php

/**
 * 
 */
class Form_Field_Dummy extends Form_Field_Html
{
	public function __construct($name, $options = array())
	{
		parent::__construct($name, new Options($options, array(
			'escape' => true,
			'validator' => Form_Validator::valid()
			)
		));
	}
	
	public function parse($submitted){}
	
	public function render()
	{
		if ($this->options['escape'])
			return tag('div', (string)$this->getValue());
		else
			return tag('div html_escape_off', (string)$this->getValue());
	}
}