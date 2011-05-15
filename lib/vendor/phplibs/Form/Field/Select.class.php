<?php

require_once __DIR__ . '/Html.class.php';

/**
 * HTML \<select\> field implementation.
 */
class Form_Field_Select extends Form_Field_Html
{
	/**
	 * Create a new seletc field
	 * @param string $name Name of the field
	 * @param array $options Supported options are:
	 * 	- multiple (Default: false): If true, then the user can select multiple values at one time.
	 *  - attribs : Associative array of extra html attributes that should be renderd on element.
	 *  - required : If this element is required.
	 *  - optionlist (@b MANDATORY) : An associative array of available options.
	 *  	You can use nested arrays to create groups. Nested groups are not permitted as
	 *  	per HTML specification.
	 *  - disabled (default false) : If true this control is disabled
	 */
	public function __construct($name, $options = array()) 
	{
		parent::__construct($name, new Options($options, array(
			'attribs' => array(),
			'multiple' => false,
			'required' => false,
			'disabled' => false,
			),
			array('optionlist')
		));

		$this->addValidator(
			Form_Validator::inArray(array_keys($this->extractValues())),
			'html');
			
		if ($this->isRequired())
			$this->addValidator(
				Form_Validator::boolAnd(
					Form_Validator::isNotEmpty(),
					$this->getValidator('default')),
				'default');
	}
	
	/**
	 * Remove groups and get only values
	 */
	private function extractValues()
	{
		$values = array();
		foreach($this->getOptionList() as $value => $label) {
			if (!is_array($label) ) {
				$values[$value] = $label;
			}
			
			foreach($label as $value => $label)
				$values[$value] = $label;
		}
		return $values;
	}
	
	/**
	 * Set the option list that will be used
	 * @param array $list  An associative array of available options.
	 *  	You can use nested arrays to create groups. Nested groups are not permitted as
	 *  	per HTML specification.
	 */
	public function setOptionList($list)
	{
		$this->options['optionlist'] = $list;
	}
	
	/**
	 * Get the option list.
	 * @return array 
	 */
	public function getOptionList()
	{
		return $this->options['optionlist'];
	}
	
	/**
	 * Shortcut to render one option of the select box
	 * @param string $value The value of the option.
	 * @param string $label The label that will be shown.
	 */
	private function renderOption($value, $label)
	{
		return tag('option html_escape_off',
				array('value' => $value),
				($value == $this->getValue())?array('selected'=>'selected'):array(),
				esc_sp(esc_html((string)$label))
			);
	}
	
	/**
	 * Render this element
	 * @param array $options
	 */
	public function render($options)
	{
		$select = tag('select ',
			$this->generateAttributes(), array('name' => $this->getHtmlFullName($options)));
		
		foreach($this->getOptionList() as $opt_key => $opt_text) {
			if (is_array($opt_text)) {
				$optgroup = tag('optgroup')->attr('label', $opt_key)->appendTo($select);			
				foreach($opt_text as $opt_key => $opt_text) {
					$this->renderOption($opt_key, $opt_text)->appendTo($optgroup);
				}
			}
			$this->renderOption($opt_key, $opt_text)->appendTo($select);
		}
		return $select;
	}
}

/**
 * Create a Form_Field_Select
 * @see Form_Field_Select
 * @return Form_Field_Select
 */
function field_select($name, $options = array()) {
	return new Form_Field_Select($name, $options);
}