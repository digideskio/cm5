<?php

require_once(__DIR__ . '/Container.class.php');

/**
 * HTML Rendable fieldset element.
 */
class Form_FieldSet extends Form_Field_Container
{
	//! A list with all options
	public $options;
	
	//! The name of this field
	protected $name;
	
	/**
	 * Create a new fieldset
	 * @param string $name The name
	 * @param array $options Support options are:
	 * 	- label (Default $this->getName()) A label to be put on this fieldset
	 */
	public function __construct($name, $options)
	{
		$this->name = $name;
		$this->options = new Options($options, array('label' => $this->name));
	}
	
	/**
	 * Set the name of this element
	 * @param string $name A name for this element
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
	/**
	 * Get the name of this element
	 * (non-PHPdoc)
	 * @see Form_Field_Container::getName()
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Render this element
	 * @param string $options Various rendering options
	 * 	and namespace tracking.
	 */
	public function render($options)
	{
		$html_name = ($this->getName() === null)
			? ''
			: (isset($options['namespace']))
				? $options['namespace'] . "[{$this->getName()}]"
				: $this->getName();
		
		etag('fieldset')->attr('name', $html_name)->push_parent();
		if (!empty($this->options['label']))
			etag('legend', $this->options['label']);
		etag('ul class="fields"')->push_parent();
		$this->walkFields(function($field, $index) use($html_name){
			etag('li')->attr('data-name', $field->getName())->push_parent();
			
			// Render field
			Output_HTMLTag::get_current_parent()->append($field->render(array(
				'namespace' => $html_name,
				'index' => $index)));
			
			// Add extra fields
			if (($field->isValid() === false) && ($field->getError()))
				etag('span class="error"', (string)$field->getError());
			else if ($field->options->has('hint'))
				etag('span class="hint"', $field->options['hint']);
			Output_HTMLTag::pop_parent();
		});
		Output_HTMLTag::pop_parent(2);
	}
}

/**
 * Shortcut to create Form_FieldSet object
 * @see Form_FieldSet::__construct()
 * @return Form_FieldSet
 */
function field_set($name, $options = array()) {
	return new Form_FieldSet($name, $options);
}