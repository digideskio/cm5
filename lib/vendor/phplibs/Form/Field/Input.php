<?php

require_once(__DIR__ . '/Html.class.php');

class Form_Field_Input extends Form_Field_Html
{
	public function __construct($name, $type, $options)
	{
		parent::__construct($name, new Options($options,
			array('type' => 'text')
		));

	}
	
	public function render()
	{
		$t = tag('input')->attr('type', $this->options['type']);
	}
}