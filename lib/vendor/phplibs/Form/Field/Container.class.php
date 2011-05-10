<?php

class Form_Field_Container
{
	protected $fields = array();
	
	/**
	 * Add a new field on this form.
	 * @param Form_Field $obj
	 */
	public function addField(Form_Field $obj)
	{	
		// Unique name field
		if (!isset($this->fields[$obj->getName()]))
			return $this->fields[$obj->getName()] = $obj;

		if (!is_array($this->fields[$obj->getName()]))
			$this->fields[$obj->getName()] = array($this->fields[$obj->getName()]);
		
		$this->fields[$obj->getName()][] = $obj;		
	}
	
	/**
	 * Add more than one field in this form
	 * @param Form_Field $obj1
	 */
	public function addFields(Form_Field $obj1)
	{	
		$args = func_get_args();
		foreach($args as $o)
			$this->addField($o);
	}
	
	/**
	 * Get a field from this form based on its unique name.
	 * @return Form_Field The actual field object or null.
	 */
	public function getField($name)
	{
		return isset($this->fields[$name])?$this->fields[$name]:null;
	}
	
	/**
	 * Get the list with all fields of this form.
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}
	
	/**
	 * Remove a field from this form
	 * @param $name The unique name of the field.
	 */
	public function removeField($name)
	{
		unset($this->fields[$name]);
	}
	
	/**
	 * Get the values of this field
	 */
	public function getValues()
	{
		$values = array();
		foreach($this->fields as $f) {
			
		}
	}
}