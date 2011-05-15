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
		if ($this->isType('range')) {
			$this->options->add('min', 0);
			$this->options->add('max', 100);
		}

		if ($this->isType('range', 'number'))
			$this->options->add('step', 1);
			
		if (!$this->options->has('rendervalue'))
			$this->options['rendervalue'] = $this->isType('password','file')
				?false
				:true;

		// Sanitize options
		if ($this->options->get('multiple') && !$this->isType('email', 'file'))
			unset($this->options['multiple']);

		// Add default validator
		$this->addValidator($this->generateDefaultValidator(), 'html');
	}
	
	/**
	 * Get the type of the input field.
	 */
	public function getType()
	{
		return $this->options['type'];
	}
	
	/**
	 * Check if this element is one of type
	 * @param string $type1
	 * @param string $typeN
	 */
	public function isType($type1)
	{
		$types = func_get_args();
		return in_array($this->getType(), $types);
	}
	
	/**
	 * Get supported constraints per type
	 * (non-PHPdoc)
	 * @see Form_Field_Html::getSupportedConstraints()
	 */
	public function getSupportedConstraints()
	{
		if ($this->isType('hidden', 'color')) {
			return array();
		} else if ($this->isType('date', 'datetime', 'datetime-local',
			'month', 'week', 'time', 'number')) {
			return array('min', 'max', 'step', 'required');
		} else if ($this->isType('range')) {
			return array('min', 'max', 'step');		
		} else if ($this->isType('checkbox', 'radio'))
			return array('required');	
		return array('required', 'maxlength', 'pattern');
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
			$validator[] = Form_Validator::matchRegex($this->options['pattern']);

		// Add max length
		if ($this->options->has('maxlength'))
			$validator[] = Form_Validator::isStrlenBetween(null, $this->options['maxlength']);

		// Add min, max, step for numbers and ranges
		if ($this->isType('number', 'range')) {
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
			$req_validator = Form_Validator::isNotEmpty('This field is required.');
				
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
	 * Overwrite parsing phase and enchance it per case.
	 * (non-PHPdoc)
	 * @see Form_Field::onParse()
	 */
	protected function onParse($submitted)
	{
		// Security: Do not change immutable fields.
		if (!$this->isMutable())
			return $this->getValue();
		
		// Explode values when multiple is set.
		if ($this->options->get('multiple')) {
			if ($this->isType('email')) {
				$values = isset($submitted[$this->getName()])
					? explode(',', $submitted[$this->getName()])
					: array();
				array_walk($values, function(& $v){ $v = trim($v); });
				// Remove empty ones
				while(($offset = array_search('', $values)) !== false)
					array_splice($values, $offset, 1);
				return $values;
			}
		}
		
		// Numbers only
		if ($this->isType('number', 'range')) {
			$value = isset($submitted[$this->getName()])
				?$submitted[$this->getName()]
				:'';
			if (!is_numeric($value))
				$value = '';
			return $value;
		}
			
		// Fall back to classic parsing
		return parent::onParse($submitted);
	}
	
	protected function applyRendableValue($input_el)
	{
		$value = $this->getValue();
		
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
	public function render($options)
	{
		// Create element
		$t = tag('input', $this->generateAttributes())
			->attr('type', $this->options['type'])
			->attr('name', $this->getHtmlFullName($options));
		
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
