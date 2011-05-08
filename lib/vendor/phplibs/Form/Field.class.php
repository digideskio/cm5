<?php

/**
 * Base class for creating a form field.
 * This is a full working without the rendering part.
 * @note Chainable
 */
class Form_Field
{
	/**
	 * The name of this field
	 * @var string
	 */
	private $name;
	
	/**
	 * The validator for this.
	 * @var callable
	 */
	private $validator;
	
	/**
	 * The value of this field.
	 * @var mixed
	 */
	private $value = null;
	
	/**
	 * All the options of this field
	 * @var Options
	 */
	public $options;
	
	/**
	 * Construct a new Field object for form
	 * @param string $name The unique name of field in form.
	 * @param array $options accept
	 * 	- label (default: $name) : The label of this field.
	 * 	- value (default: null) : The default value of this field.
	 * 	- validator (default: isNotEmpty) : A callable object to validate this field.
	 * 	- onerror (default: This field is invalid) : The error message of this field.
	 */
	public function __construct($name, $options = array())
	{
		$this->name = $name;
		
		$this->options = new Options($options, array(
			'value' => null,
			'label' => $this->name,
			'validator' => Form_Validator::isNotEmpty(),
			'onerror' => 'This field is invalid.'
		));
		
		$this->value = $this->options['value'];
		$this->validator = $this->options['validator'];
	}
	
	/**
	 * Get the value of an option.
	 * @param string $name The key name of option.
	 */
	public function getOption($name)
	{
		return $this->options->get($name);
	}
	
	/**
	 * Set the value of an option.
	 * @param string $name The key name of option.
	 * @param string $value The value of the option.
	 */
	public function setOption($name, $value)
	{
		return $this->options->set($name);
	}
	
	/**
	 * Get the name of this field.
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Get the value of this field.
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * Set the value of this field.
	 * @param mixed $value The value as it is intended to be parsed.
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}
	
	/**
	 * Parse the submitted data and extra value for this field
	 * @param array $submitted Data submitted (posted).
	 */
	public function parse($submitted)
	{
		$this->value = isset($submitted[$this->name])?$submitted[$this->name]:null;
		$this->options->remove('valid');
		return $this;
	}
	
	public function process($submitted)
	{
		$this->parse($submitted);
		$this->options['valid'] = (is_callable($this->validator))
				? call_user_func($this->validator, $this)
				: false;
	}
	
	/**
	 * Check if this field is valid by executing the validator.
	 */
	public function isValid()
	{
		return $this->options->get('valid');
	}
	
	/**
	 * Define a validator for this field
	 * @param callable $callable
	 */
	public function setValidator($callable)
	{
		$this->validator = $callable;
		return $this;
	}
	
	/**
	 * Get the current validator of this field
	 * @return mixed The validator or null if noone is set.
	 */
	public function getValidator()
	{
		return $this->validator;
	}
}

