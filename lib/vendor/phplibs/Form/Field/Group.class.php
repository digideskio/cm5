<?php


class Form_Field_Group extends Form_Field_Html
{
	protected $fields = array();
	
	public function addField(Form_Field $field)
	{
		$this->fields[$field->name] = $field;
	}
	
	public function getFields()
	{
		return $this->fields;
	}
	
	public function removeField($name)
	{
		unset($this->fields[])
	}
	
	public function render()
	{
		return etag('fieldset',
			tag('legend', $this->options['label'])
		);
	}
}