<?php

/**
 * A raw field that can display any kind of html.
 * It will render the "value"
 */
class Form_Field_Raw extends Form_Field_Html
{
	/**
	 * No special options
	 * @param string $name
	 * @param array $options
	 */
	public function __construct($name, $options = array())
	{
		parent::__construct($name, new Options($options, array(
			'escape' => true,
			'label' => ''
			)
		));
		
		$this->removeValidator('html');
	}
	
	
	/**
	 * No parsing
	 * (non-PHPdoc)
	 * @see Form_Field_Html::onParse()
	 */
	protected function onParse($submitted)
	{
		return $this->getValue();
	}
	
	/**
	 * Raw field is always valid.
	 * (non-PHPdoc)
	 * @see Form_Field::isValid()
	 */
	public function isValid()
	{
		return true;
	}
	
	/**
	 * Render the data of value in a div.
	 */
	public function render($options)
	{
		return ($this->getValue() instanceof Output_HTMLTag)
			?$this->getValue()
			:tag('div ' . (!$this->options['escape']?' html_escape_off':''), (string)$this->getValue());
	}
}


/**
 * Create a Form_Field_Raw
 * @see Form_Field_Raw
 * @return Form_Field_Raw
 */
function field_raw($name, $options = array()) {
	return new Form_Field_Raw($name, array_merge($options));
}