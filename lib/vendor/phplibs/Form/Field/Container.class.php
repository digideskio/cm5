<?php

require_once(__DIR__ . '/Interface.class.php');
require_once(__DIR__ . '/SameNameContainer.class.php');

/**
 * Base class to implement "fields container" concept. 
 */
class Form_Field_Container implements Form_Field_Interface
{
	//! List with al fields of container.
	protected $fields = array();
	
	/**
	 * Add a new field on this form.
	 * @note This function implements "chainable" concept.
	 * @param Form_Field $obj
	 */
	public function add(Form_Field_Interface $obj)
	{	
		// Unique name field
		if (!isset($this->fields[$obj->getName()]))
			return $this->fields[$obj->getName()] = $obj;

		if (! $this->fields[$obj->getName()] instanceof Form_Field_SameNameContainer)
			$this->fields[$obj->getName()] =
				new Form_Field_SameNameContainer(array($this->fields[$obj->getName()]), $obj->getName());
		
		$this->fields[$obj->getName()]->append($obj);
		return $this;		
	}
	
	/**
	 * Add more than one field in this form
	 * @note This function implements "chainable" concept.
	 * @param Form_Field $obj1
	 */
	public function addMany(Form_Field_Interface $obj1)
	{	
		$args = func_get_args();
		foreach($args as $o)
			$this->add($o);
		return $this;
	}
	
	/**
	 * Get a field from this form based on its unique name.
	 * @return Form_Field The actual field object or null.
	 */
	public function get($name)
	{
		return isset($this->fields[$name])
			? $this->fields[$name]
			: null;
	}
	
	/**
	 * Get the list with all fields of this form.
	 * @return array
	 */
	public function getMany()
	{
		return $this->fields;
	}
	
	/**
	 * Remove a field from this form
	 * @param $name The unique name of the field.
	 */
	public function remove($name)
	{
		unset($this->fields[$name]);
	}
	
	/**
	 * Container returns null name by default
	 * (non-PHPdoc)
	 * @see Form_Field_Interface::getName()
	 */
	public function getName()
	{
		return null;
	}
	
	/**
	 * Get the value of this container
	 * (non-PHPdoc)
	 * @see Form_Field_Interface::getValue()
	 */
	public function getValue()
	{
		return $this->getValues();
	}
	
	/**
	 * Get the values of fields in an associative array.
	 */
	public function getValues()
	{
		$values = array();
		foreach($this->fields as $name => $field)
			$values[$name] = $field->getValue();			
		return $values;
	}
	
	/**
	 * A function to walk through all fields
	 * @param callable $callable A function to be called with parameters
	 *  callable($field, $multiple).
	 *  - $field Is one object of the iteration
	 *  - $index: In case that there are multiple fields with same
	 *  	name this is their index. NULL if it is unique.
	 *  .
	 */
	public function walkFields($callable)
	{
		foreach($this->fields as $field) {
			if (!$field instanceof Form_Field_SameNameContainer) {
				$callable($field, null);
			} else {
				foreach($field as $index => $subfield)
					$callable($subfield, $index);
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
	 * Return null error by default
	 * (non-PHPdoc)
	 * @see Form_Field_Interface::getError()
	 */
	public function getError()
	{
		return null;
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
	
	/**
	 * (non-PHPdoc)
	 * @see Form_Field_Interface::process()
	 */
	public function process($submitted)
	{
		$subvalues = ($this->getName() == null)
			? $submitted
			: ((isset($submitted[$this->getName()]))
				?$submitted[$this->getName()]
				:array());

		$this->walkFields(function($field, $index) use($subvalues){
			$field->process($subvalues);
		});
	}
	
}
