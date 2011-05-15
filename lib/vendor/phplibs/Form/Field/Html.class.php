<?php

require_once(__DIR__ . '/../Field.class.php');

/**
 * Base class for HTML rendable fields.
 * Implement all basic tools to create full html
 * compliant fields.
 */
class Form_Field_Html extends Form_Field
{
	
	public function __construct($name, $options)
	{
		parent::__construct($name, $options);
		
		// Add 'html' validator
		if (!$this->isBaredFromValidation()) {
			$this->addValidator($this->getConstraintsValidator(),
				'html');
		}
	}
	
	/**
	 * Get an array of the per specification constraints
	 * that are supported by this element.
	 * @note This should be overloaded by derivatives.
	 * @return array Default array('required');
	 */
	public function getSupportedConstraints()
	{
		return array('required');
	}
	
	/**
	 * Generate a complete validator for these constraints
	 */
	public function getConstraintsValidator()
	{
		$constraints = array();
		
		foreach($this->getSupportedConstraints() as $const) {
			if ($const == 'required') {
				$constraints[] = Form_Validator::isNotEmpty(); 
			} else if ($const == 'pattern') {
				if ($this->options->has('pattern'))
					$constraints[] = Form_Validator::matchRegex($this->options['pattern']);
			} else if ($const == 'maxlength') {
				if ($this->options->has('maxlength'))
					$constraints[] = Form_Validator::isStrlenBetween(null, $this->options['maxlength']);
			}
		}
		
		// Cannot be 
		if (empty($constraints))
			$constraints = Form_Validator::valid();
		else if (count($constraints) > 1)
			$constraints = call_user_func_array(
				array('Form_Validator', 'boolAnd'), $constraints);
		else
			$constraints = $constraints[0];
			
		return $constraints;		
	}
	
	/**
	 * Get the full name that must be rendered inside field.
	 * The name dependes on previous fields "namespace" and
	 * if its name is used "multiple" times.
	 * @param string $options Options to be used
	 */
	public function getHtmlFullName($options)
	{
		$html_name = ($this->getName() === null)
			? ''
			: (isset($options['namespace']) && $options['namespace'])				
				? $options['namespace'] . "[{$this->getName()}]"
				: $this->getName();
		
		// In case of multiple with same we use [] and not [$index]
		// as the second will produce different names and controls that
		// need it like radio buttons will not work.
		if (isset($options['index']) && ($options['index'] !== null))
			$html_name = $html_name != ''
				? $html_name . '[]'
				: '';
		return $html_name;
	}
	
	/**
	 * Generate attributes from field options and the
	 * user provided ones (options[attribs).
	 * @return array
	 */
	protected function generateAttributes()
	{
		$attribs = array();
		
		// Move options to attrbitues
		foreach(array('min', 'max', 'step', 'maxlength') as $attr)
			if ($this->options->has($attr))
				$attribs[$attr] = $this->options[$attr];
		foreach(array('required', 'readonly', 'disabled', 'multiple') as $attr)
			if ($this->options->get($attr))
				$attribs[$attr] = $attr;
		
		// Add pattern if exists
		if ($this->options->has('pattern')) {
			$html_pattern = trim($this->options['pattern'], '^$' . $this->options['pattern'][0]);
			$attribs['pattern'] = $html_pattern;
		}
		return array_merge($this->options['attribs'], $attribs);
	}
	
	/**
	 * Overide parsing to skip when object is immutable
	 */
	protected function onParse($submitted)
	{
		// Dont change immutable
		if (!$this->isMutable())
			return $this->getValue();
			
		return parent::onParse($submitted);		
	}
	
	/**
	 * Return true if element is disabled
	 */
	public function isDisabled()
	{
		return (boolean)$this->options->get('disabled');
	}
	
	/**
	 * Change the status of element
	 * @param boolean $state The new status. If true then element
	 * will be disabled. If false it will be enabled.
	 */
	public function setDisabled($state)
	{
		return $this->options->set('disabled', (boolean)$state);
	}
	
	/**
	 * Return true if this element is readonly
	 */
	public function isReadonly()
	{
		return (boolean)$this->options->get('readonly');
	}
	
	
	/**
	 * Change the readonly status of element
	 * @param boolean $state The new status. If true then element
	 * will be readonly. If false it will be editable.
	 */
	public function setReadonly($state)
	{
		return $this->options->set('readonly', (boolean)$state);
	}
	
	/**
	 * In generic case mutable is when it is not disabled
	 * and not readonly
	 */
	public function isMutable()
	{
		return !($this->isDisabled() || $this->isReadonly());
	}
	
	/**
	 * Check if this element is required
	 */
	public function isRequired()
	{
		return (boolean)$this->options->get('required');
	}
	
	/**
	 * Change if this field is required by user
	 * @param boolean $state The new status. If true then element
	 * will be required. If false it will be optional.
	 */
	public function setRequired($state)
	{
		return $this->options->set('required', (boolean)$state);
	}
	
	/**
	 * Check if this element is bared from validation
	 */
	public function isBaredFromValidation()
	{
		return $this->isDisabled();
	}
}