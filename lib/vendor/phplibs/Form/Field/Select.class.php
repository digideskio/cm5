<?php

require_once(__DIR__ . '/./Html.class.php');

class Form_Field_Select_Html extends Form_Field_Html {}

class Form_Field_Select extends Form_Field_Container
{
	public function __construct($name, $options = array()) 
	{
		parent::__construct($name, new Options($options, array(
			'attribs' => array(),
			'multiple' => false
			),
			array('optionlist')
		));

		$this->addValidator(
			Form_Validator::inArray(array_keys($this->options['optionlist'])));
	}
	
	public function render()
	{
		$select = tag('select ', $this->options['attribs'], array('name' => $this->getName()));
		
		foreach($this->options['optionlist'] as $opt_key => $opt_text) {
			tag('option html_escape_off',
				array('value'=>$opt_key),
				($opt_key == $this->getValue())?array('selected'=>'selected'):array(),
				esc_sp(esc_html((string)$opt_text))
			)->appendTo($select);
		}
		return $select;
	}
}

function field_select($name, $options = array()) {
	return new Form_Field_Select($name, $options);
}