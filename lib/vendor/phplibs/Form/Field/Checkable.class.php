<?php

require_once(__DIR__ . '/Input.class.php');

/**
 * HTML5 Implemenetation of input[type=checkbox] and input[type=radio] types.
 */
class Form_Field_Checkable extends Form_Field_Input
{
	/**
	 * Construct a new file field.
	 * @see field_checkbox() and field_radio() for shortcut
	 * @see Form_Field_Input::__construct() for more options.
	 * @param string $name The name of the field
	 * @param array $options Available options are:
	 *  - type (Default checkbox) : radio or checkbox.
	 * 	- multiple (Default false) : If true and the browser supports it, field
	 * 		can accept and manage multiple files at once.
	 *  - required (Default false) : If true then this field is required.
	 *  - checked : For boolean state controls like radio and checkbox
	 *  - value : Will set the option value of this checkable field.
	 *  .
	 */
	public function __construct($name, $options = array())
	{
		parent::__construct($name, new Options($options,
			array('type' => 'checkbox')));
		
		// Normalize optionvalue
		if (in_array($this->getType(), array('radio', 'checkbox')))
			$this->setOptionValue(($this->getOptionValue() === null)?'on':$this->getOptionValue());
		
		if ($this->options->get('required'))
			$this->addValidator(Form_Validator::isChecked('This field is required.'),
				'default');			
		
	}

	/**
	 * Check if control is checked
	 */
	public function isChecked()
	{
		return $this->options->get('checked');
	}
	
	/**
	 * Check if control is checked
	 * @param boolean $checked Set the checked state.
	 */
	public function setChecked($checked)
	{
		return $this->options['checked'] = (boolean)$checked;
	}	
	
	/**
	 * Checkable object are mutable independatly from "readonly" field.
	 * (non-PHPdoc)
	 * @see Form_Field_Html::isMutable()
	 */
	public function isMutable()
	{
		return !$this->isDisabled();
	}
	
	protected function onParse($submitted)
	{
		// Dont change immutable
		if (!$this->isMutable())
			return $this->getOptionValue();

		$this->options['checked'] =
			isset($submitted[$this->getName()])
				? is_array($submitted[$this->getName()])	// multiple values
					? in_array($this->getOptionValue(), $submitted[$this->getName()])
					: $submitted[$this->getName()] == $this->getOptionValue()
				: false;	// not setted
				
		return $this->getOptionValue();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Form_Field_Input::render()
	 * @return Output_HTMLTag html element.
	 */
	public function render($options)
	{
		$input_el = parent::renderInput($options);
		$input_el->attr('value', $this->getOptionValue());		
		if ($this->isChecked())
			$input_el->attr('checked', 'checked');
		
		return tag('label',	$input_el, $this->options['label']);
	}
	
	/**
	 * Set the value that will be returned if object
	 * is checked.
	 * @param string $value
	 * @see getValue() isChecked() setChecked()
	 */
	public function setOptionValue($value)
	{
		parent::setValue($value);
	}
	
	/**
	 * Get the current option value. This is the value
	 * that will be returned if object is checked.
	 * @see getValue() isChecked() setChecked()
	 */
	public function getOptionValue()
	{
		return parent::getValue();
	}
	
	/**
	 * Hide setValue from interface.
	 * @see setChecked()
	 * @see setOptionValue()
	 */
	public function setValue()
	{
		throw new RuntimeException('Dont use Checkable::setValue(). Use setChecked() or setOptionValue().');
	}
	
	/**
	 * If the field is "checked" then it will return the option-value
	 * eitherwise it will return null
	 * (non-PHPdoc)
	 * @see Form_Field::getValue()
	 */
	public function getValue()
	{
		return $this->isChecked()
			? parent::getValue()
			: null;
	}
}

/**
 * Create a Form_Field_Checkable with type="checkbox"
 * @see Form_Field_Checkable
 * @return Form_Field_Checkable
 */
function field_checkbox($name, $options = array()) {
	return new Form_Field_Checkable($name, array_merge($options, array('type' => 'checkbox')));
}

/**
 * Create a Form_Field_Input with type="radio"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_radio($name, $options = array()) {
	return new Form_Field_Checkable($name, array_merge($options, array('type' => 'radio')));
}
