<?php

/**
 * This class is used by Form_Field_Container to group
 * fields of same name. You don't need to use it directly.
 * However it will be returned if you ask for a field
 * that does not have unique name
 */
class Form_Field_SameNameContainer extends ArrayObject
{	
	//! The name of the subfields
	private $name;
	
	/**
	 * Construct a new subfields container
	 * @param string $array The array of fields
	 * @param string $name The name that all fields share.
	 */
	public function __construct($array, $name)
	{
		parent::__construct($array);
		$this->name = $name;
	}
	
	/**
	 * Get the name of all subfields (it is the same).
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Get an array with all fields values.
	 */
	public function getValue()
	{
		$values = array();
		foreach($this as $subfields)
			if ($subfields->getValue() !== null)
				$values[] = $subfields->getValue();
		return $values;
	}
	
	/**
	 * Get fields by option value
	 * @return Form_Field_Checkable
	 */
	public function getByOptionValue($optionvalue)
	{
		foreach($this as $field)
			if ($field->getOptionValue() == $optionvalue)
				return $field;
	}
}
