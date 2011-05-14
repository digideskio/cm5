<?php

require_once(__DIR__ . '/Html.class.php');

/**
 * <Input> HTML Element implementation. It supports
 * all the known types till HTML5. It generates
 * client side HTML5 validation and server side,
 * providing security and backwards compatibility
 * with HTML4 browsers.
 */
class Form_Field_Input extends Form_Field_Html
{
	private $supported_types = array(
		'button', 'checkbox', 'color', 'date', 'datetime', 'datetime-local',
		'month', 'week', 'time', 'email', 'file', 'hidden', 'image', 'number',
		'password', 'radio', 'range', 'reset', 'search', 'submit', 'tel', 'text', 'url');
	
	/**
	 * Create a new Form_Field_Input field
	 * @param string $name The unique name of this field.
	 * @param array $options Supported options are:
	 * 	- type : All HTML5 input type.
	 *  - min : Minimum value for "range", "date/time" and "number" type.
	 *  - max : Maximum value for "range", "date/time" and "number" type.
	 *  - step : The step size used by "range", "date/time" and "number".
	 *  - pattern : Defines the pattern as an HTML5 attribute and adds server-side validator.
	 *  	Notice that / must be escaped because they are used as delimiters in server side validation. 
	 *  - required : If true this field is marked as required and a server validator is added.
	 *  - readonly : If you want the field to be readonly
	 *  - rendervalue (Default : true any / false for password,file) : Setting true it will render the value in
	 *  	the element, on false it will skip it.
	 *  - checked : For boolean state controls like radio and checkbox
	 *  .
	 */
	public function __construct($name, $options)
	{
		parent::__construct($name, new Options($options,
			array('type' => 'text', 'attribs' => array())
		));
		
		// Check for supported types
		if (!in_array($this->getType(), $this->supported_types))
			throw new InvalidArgumentException("\"{$this->getType()}\" is not a supported type by Form_Field_Input.");

		// Add HTML5 default values
		if ($this->getType() == 'range') {
			$this->options->add('min', 0);
			$this->options->add('max', 100);
		}
		if (in_array($this->getType(), array('radio', 'checkbox')))
			$this->setValue(($this->getValue() === null)?'on':$this->getValue());
		if (in_array($this->getType(), array('range', 'number')))
			$this->options->add('step', 1);
			
		if (!$this->options->has('rendervalue'))
			$this->options['rendervalue'] = in_array($this->getType(), array('password','file'))?false:true;

		// Sanitize options
		if ($this->options->get('multiple') && !in_array($this->getType(), array('email', 'file')))
			unset($this->options['multiple']);

		// Move options to attrbitues
		foreach(array('min', 'max', 'step', 'pattern', 'maxlength') as $attr)
			if ($this->options->has($attr))
				$this->options['attribs'][$attr] = $this->options[$attr];
		foreach(array('required', 'readonly', 'disabled', 'multiple') as $attr)
			if ($this->options->has($attr))
				$this->options['attribs'][$attr] = $attr;
		
		// Add default validator
		$this->addValidator($this->generateDefaultValidator(), 'default');
	}
	
	/**
	 * Get the type of the input field.
	 */
	public function getType()
	{
		return $this->options['type'];
	}
	
	/**
	 * Generated the server-side default validator as per HTML5 specification.
	 */
	protected function generateDefaultValidator()
	{
		$type = $this->getType();
		
		// Create basic type validator
		if ('url' == $type) {
			$validator[] = Form_Validator::isUrl();
		} else if ('email' == $type) {
			$validator[] = Form_Validator::isEmail();
		} else if ('url' == $type) {
			$validator[] = Form_Validator::isUrl();
		} else {
			$validator[] = Form_Validator::valid();
		}
		
		// Add pattern matching
		if ($this->options->has('pattern'))
			$validator[] = Form_Validator::matchRegex('/' . trim($this->options['pattern'], ' ^$') . '/');

		// Add max length
		if ($this->options->has('maxlength'))
			$validator[] = Form_Validator::isStrlenBetween(null, $this->options['maxlength']);

		// Add min, max, step for numbers and ranges
		if (($type == 'number') || ($type == 'range')) {
			if ($this->options->has('min') || $this->options->has('max'))
				$validator[] = Form_Validator::isNumberBetween($this->options->get('min'), $this->options->get('max'));
			if ($this->options->has('min') || $this->options->has('max'))
				$validator[] = Form_Validator::isNumberBetween($this->options->get('min'), $this->options->get('max'));				
			if ($this->options->has('step')) {
				$validator[] = Form_Validator::isNumberQuantized($this->options['step'], $this->options->get('min'));
			}
		}
			
		// Merge validators with required and multiple
		if ($this->options->get('required')) {
			$req_validator = (in_array($this->getType(), array('radio', 'checkbox')))
				?Form_Validator::isChecked('This field is required.')
				:Form_Validator::isNotEmpty('This field is required.');
				
			if ($this->options->get('multiple')) {
				$validator = call_user_func_array(array('Form_Validator', 'boolAnd'), $validator);
				$validator = Form_Validator::eachElement($validator);
				$validator = Form_Validator::boolAnd($req_validator, $validator);				
			} else {
				array_unshift($validator, $req_validator);			
				$validator = call_user_func_array(array('Form_Validator', 'boolAnd'), $validator);
			}
		} else {
			if ($this->options->get('multiple')) {
				$validator = Form_Validator::boolOr(
					Form_Validator::isEmpty(),
					Form_Validator::eachElement(call_user_func_array(array('Form_Validator', 'boolAnd'), $validator))
			);
			} else {
				$validator = Form_Validator::boolOr(
					Form_Validator::isEmpty(),
					call_user_func_array(array('Form_Validator', 'boolAnd'), $validator)
				);
			}
		}
		
		return $validator;
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
	 * Overwrite parsing phase and enchance it per case.
	 * (non-PHPdoc)
	 * @see Form_Field::onParse()
	 */
	protected function onParse($submitted)
	{
		// Security: Do not accept new values from readonly fields.
		if ($this->options->get('readonly') || $this->options->get('disabled'))
			return $this->getValue();
		
		// Explode values when multiple is set.
		if ($this->options->get('multiple')) {
			if ($this->getType() == 'email') {
				$values = explode(',', $submitted[$this->getName()]);
				array_walk($values, function(& $v){ $v = trim($v); });
				// Remove empty ones
				while(($offset = array_search('', $values)) !== false)
					array_splice($values, $offset, 1);
				return $values;
			}
		}
		
		// On/Off state
		if (in_array($this->getType(), array('checkbox', 'radio'))) {
			$this->options['checked'] = isset($submitted[$this->getName()]) 
				&& ($submitted[$this->getName()] == $this->getValue());
			var_dump($this->getValue(), $this->isChecked());
			return $this->getValue();
		}
		
		// Numbers only
		if (in_array($this->getType(), array('number', 'range'))) {
			$value = $submitted[$this->getName()];
			if (!is_numeric($value))
				$value = '';
			return $value;
		}
			
		// Fall back to classic parsing
		return isset($submitted[$this->getName()])
			? $submitted[$this->getName()]
			: null;
	}
	
	protected function applyRendableValue($input_el)
	{
		$value = $this->getValue();
		
		// Attribute based values
		if (in_array($this->getType(), array('checkbox', 'radio'))) {
			if ($this->isChecked())
				$input_el->attr('checked', 'checked');
		}
		
		// Multiple data
		if ($this->options->get('multiple')) {
			if (is_array($value))
				$value = implode(',', $value);
		}
		
		$input_el->attr('value', $value);
	}
	
	/**
	 * Render this input box.
	 */
	public function render()
	{
		// Create element
		$t = tag('input', $this->options['attribs'])
			->attr('type', $this->options['type'])
			->attr('name', $this->getName());
		
		// Render value in element
		if ($this->options['rendervalue'])
			$this->applyRendableValue($t);
		return $t;
	}
}

/**
 * Create a Form_Field_Input
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_input($name, $options = array()) {
	return new Form_Field_Input($name, $options);
}

/**
 * Create a Form_Field_Input with type="text"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_text($name, $options = array()) {
	return new Form_Field_Input($name, array_merge($options, array('type' => 'text')));
}

/**
 * Create a Form_Field_Input with type="email"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_email($name, $options = array()) {
	return new Form_Field_Input($name, array_merge($options, array('type' => 'email')));
}

/**
 * Create a Form_Field_Input with type="url"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_url($name, $options = array()) {
	return new Form_Field_Input($name, array_merge($options, array('type' => 'url')));
}

/**
 * Create a Form_Field_Input with type="password"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_password($name, $options = array()) {
	return new Form_Field_Input($name, array_merge($options, array('type' => 'password')));
}

/**
 * Create a Form_Field_Input with type="hidden"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_hidden($name, $options = array()) {
	return new Form_Field_Input($name, array_merge($options, array('type' => 'hidden')));
}

/**
 * Create a Form_Field_Input with type="range"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_range($name, $options = array()) {
	return new Form_Field_Input($name, array_merge($options, array('type' => 'range')));
}

/**
 * Create a Form_Field_Input with type="number"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_number($name, $options = array()) {
	return new Form_Field_Input($name, array_merge($options, array('type' => 'number')));
}

/**
 * Create a Form_Field_Input with type="search"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_search($name, $options = array()) {
	return new Form_Field_Input($name, array_merge($options, array('type' => 'search')));
}

/**
 * Create a Form_Field_Input with type="tel"
 * @see Form_Field_Input
 * @return Form_Field_Input
 */
function field_tel($name, $options = array()) {
	return new Form_Field_Input($name, array_merge($options, array('type' => 'tel')));
}
