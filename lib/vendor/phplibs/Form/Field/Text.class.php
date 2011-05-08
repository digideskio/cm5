<?php

require_once(__DIR__ . '/./Html.class.php');

class Form_Field_Text extends Form_Field_Html
{
	public function __construct($name, $options) 
	{
		parent::__construct($name, new Options($options, array(
			'attribs' => array(),
			'multiline' => false,
			'hidden' => false,
			'password' => false
		)));
		
		// Extra options if textarea
		if ($this->options['multiline'])
			$this->options['attribs'] =  array_merge(
				$this->options['attribs'], array('rows' => 8, 'cols' => 70));
		
	}
	
	public function render()
	{
		if ($this->options['multiline'])
			$t = tag('textarea ', (string)$this->getValue());
		else if ($this->options['hidden'])
			$t = tag('input type="hidden"')->attr('value', (string)$this->getValue());
		else if ($this->options['password'])
			$t = tag('input type="password"');
		else {
			$t = tag('input type="text"')->attr('value', (string)$this->getValue());
		}
			
		$t->attr('name', $this->getName());
		foreach($this->options['attribs'] as $k => $v)
			$t->attr($k, $v);
		
		if ($this->options->has('hint'))
			$t->attr('placeholder', $this->options['hint']);
		return $t;
	}
}