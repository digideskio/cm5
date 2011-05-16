<?php

require_once(__DIR__ . '/Html.class.php');

/**
 * HTML5 Implemenetation of "textarea" field.
 */
class Form_Field_Textarea extends Form_Field_Html
{
	/**
	 * Construct a new textarea field
	 * @see field_textarea() for shortcut
	 * @param string $name The name of the field
	 * @param array $options Available options are:
	 *  - pattern : Add server side validation for regular expression. 
	 *  - required : If true this field is marked as required and a server validator is added.
	 *  - readonly : If you want the field to be readonly
	 *  - rendervalue (Default : true any / false for password,file) : Setting true it will render the value in
	 *  	the element, on false it will skip it.
	 *  - cols : The maximum number of columns.
	 *  - rows : The maximum number of rows.
	 *  .
	 */
	public function __construct($name, $options = array())
	{
		parent::__construct($name, new Options($options, array(
			'required' => false, 
			'pattern' => false,
			'attribs' => array())));
		
		// Add default columns and rows.
		if (!isset($this->options['attribs']['cols']))
			$this->options['attribs']['cols'] = 70;
		if (!isset($this->options['attribs']['rows']))
			$this->options['attribs']['rows'] = 8;
			
	}
	
	/**
	 * Get supported constraints
	 * (non-PHPdoc)
	 * @see Form_Field_Html::getSupportedConstraints()
	 */
	public function getSupportedConstraints()
	{
		return array('required', 'maxlength', 'pattern');
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Form_Field_Input::render()
	 * @return Output_HTMLTag html element.
	 */
	public function render($namespace)
	{
		$t = tag('textarea', $this->generateAttributes())
			->attr('name', $this->getHtmlFullName($namespace))
			->append((string)$this->getValue());
		
		if ($this->options->get('required'))
			$t->attr('required', 'required');
		
		return tag('label', tag('span', $this->options['label']), $t);
	}
}

/**
 * Create a Form_Field_Textarea 
 * @see Form_Field_Textarea
 * @return Form_Field_Textarea
 */
function field_textarea($name, $options = array()) {
	return new Form_Field_Textarea($name, array_merge($options));
}