<?php


class Form_FieldSet extends Form_Field_Container implements Form_Field_Interface
{
	public $options;
	
	public $name;
	
	public function __construct($name, $options)
	{
		$this->name = $name;
		$this->options = new Options($options, array('label' => $this->name));
	}
	
	public function process($submitted)
	{
		$this->walkFields(function($field) use($submitted){
			$field->process($submitted);
		});
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getValue()
	{
		return $this->getValues();
	}
	
	public function getError()
	{
		return null;
	}
	
	public function render()
	{
		etag('fieldset')->push_parent();
		if (!empty($this->options['label']))
			etag('legend', $this->options['label']);
		$this->walkFields(function($field){
			etag('li')->attr('data-name', $field->getName())->push_parent();
			
			etag('label', $field->options['label']);
			
			// Render field
			Output_HTMLTag::get_current_parent()->append($field->render());
			
			// Add extra fields
			if (($field->isValid() === false) && ($field->getError()))
				etag('span class="ui-form-error"', (string)$field->getError());
			else if ($field->options->has('hint'))
				etag('span class="ui-form-hint"', $field->options['hint']);
			Output_HTMLTag::pop_parent();
		});
		Output_HTMLTag::pop_parent();
	}
}

function field_set($name, $options = array()) {
	return new Form_FieldSet($name, $options);
}