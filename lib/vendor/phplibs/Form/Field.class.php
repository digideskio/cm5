<?php

require_once(__DIR__ . '/Validator.class.php');

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
	 * Array with all validators for this field
	 * @var array
	 */
	private $validators = array();
	
	/**
	 * Result from last validation
	 * @var boolean
	 */
	private $valid = null;
	
	/**
	 * In case of unsuccessfull validatio, this will hold the error message
	 * @var string
	 */
	private $error = null;
	
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
	 */
	public function __construct($name, $options = array())
	{
		$this->name = $name;
		
		$this->options = new Options($options, array(
			'value' => null,
			'label' => $this->name,
			'validator' => Form_Validator::isNotEmpty()
		));
		
		$this->value = $this->options['value'];
		$this->addValidator($this->options['validator'], 'default');
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
	 * @param mixed $value
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
	protected function onParse($submitted)
	{
		return isset($submitted[$this->name])?$submitted[$this->name]:null;		
	}
	
	/**
	 * Implementation of how to validate a value.
	 */
	protected function onValidate($value)
	{
		// Validate field
		if (empty($this->validators))
			return false;
		
		// valid = val1 AND val2 AND ... valN
		foreach($this->validators as $validator) {
			if (!is_callable($validator) || (!$validator($this->getValue(), $this->error, $this)))
				return false;
		}
		return true;
	}
	
	/**
	 * Process submitted data by parsing them and validating result.
	 * @param array $submitted
	 */
	public function process($submitted)
	{
		$this->value = $this->onParse($submitted);
		$this->valid = $this->onValidate($this->getValue());
	}
		
	/**
	 * Check if this field is valid by executing the validator.
	 */
	public function isValid()
	{
		return $this->valid;
	}
	
	/**
	 * Add a new validator for this field.
	 * @param callable $callable
	 * @return mixed The slot of the validator or NULL on error.
	 */
	public function addValidator($callable, $slot = null)
	{
		if ($slot == null)
			$slot = empty($this->validators)?0:max(array_keys($this->validators)) + 1;
		$this->validators[$slot] = $callable;
		return $slot;
	}
	
	/**
	 * Get the current validators for this field.
	 * @return mixed Array with all validators.
	 */
	public function getValidators()
	{
		return $this->validators;
	}
	
	/**
	 * Get a validator on a specific slot.
	 * @param $slot The slot that was returned from addValidator function
	 * @return mixed The validator at $slot or NULL if not found.
	 */
	public function getValidator($slot)
	{
		return isset($this->validators[$slot])?$this->validators[$slot]:null;
	}
	
	/**
	 * Get the error message after failed validation.
	 */
	public function getError()
	{
		return $this->error;
	}
	
	/**
	 * Forcefully invalidate this field.
	 * @param string $error The reason for being invalidated.
	 */
	public function invalidate($error)
	{
		$this->error = $error;
		$this->valid = false;
	}
}

