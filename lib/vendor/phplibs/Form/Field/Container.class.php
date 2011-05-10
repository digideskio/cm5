<?php

require_once(__DIR__ . '/Interface.class.php');

/**
 * Base class to implement "fields container" concept. 
 */
class Form_Field_Container
{
	//! List with al fields of container.
	protected $fields = array();
	
	/**
	 * Add a new field on this form.
	 * @note This function implements "chainable" concept.
	 * @param Form_Field $obj
	 */
	public function addField(Form_Field_Interface $obj)
	{	
		// Unique name field
		if (!isset($this->fields[$obj->getName()]))
			return $this->fields[$obj->getName()] = $obj;

		if (!is_array($this->fields[$obj->getName()]))
			$this->fields[$obj->getName()] = array($this->fields[$obj->getName()]);
		
		$this->fields[$obj->getName()][] = $obj;
		return $this;		
	}
	
	/**
	 * Add more than one field in this form
	 * @note This function implements "chainable" concept.
	 * @param Form_Field $obj1
	 */
	public function addFields(Form_Field_Interface $obj1)
	{	
		$args = func_get_args();
		foreach($args as $o)
			$this->addField($o);
		return $this;
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
	 * Get the values of fields in an associative array.
	 */
	public function getValues()
	{
		$values = array();
		foreach($this->fields as $name => $field) {
			if (!is_array($field)) {
				$values[$name] = $field->getValue();				
			} else {
				$values[$name] = array();
				foreach($field as $subfields)
					$values[$name][] = $subfields->getValue();
			}
		}
		return $values;
	}
	
	/**
	 * A function to walk through all fields
	 * @param callable $callable A function to be called with parameters
	 *  ($field)
	 */
	public function walkFields($callable)
	{
		foreach($this->fields as $field) {
			if (!is_array($field)) {
				$callable($field);
			} else {
				foreach($field as $subfield)
				$callable($subfield);
			}
		}
	}
	
	/**
	 * Check if all fields are valid
	 */
	public function isValid()
	{
		$valid = true;
	   	$this->walkFields(function($field) use(& $valid){	  
	   		if (!$field->isValid())
				$valid = false;
	   	});
	   	return $valid;
	}
	
	/**
	 * Get the desired encoding type. It will search in all
	 * fields and get the one with the highest priority.
	 */
	public function getEncodingType()
	{
		$enctype = Form_Field_Interface::ENCTYPE_AUTO;
		$this->walkFields(function(Form_Field_Interface $field) use(&$enctype) {
			if ($field->getEncodingType() > $enctype)
				$enctype = $field->getEncodingType();
		});
		return $enctype;
	}
}
