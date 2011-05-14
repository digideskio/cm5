<?php

require_once(__DIR__ . '/Field/Input.class.php');
require_once(__DIR__ . '/Field/Checkable.class.php');
require_once(__DIR__ . '/Field/File.class.php');
require_once(__DIR__ . '/Field/Select.class.php');
require_once(__DIR__ . '/FieldSet.class.php');

/**
 * HTML Rendered for forms.
 */
class Form_Html extends Form
{
	//! Encoding type string for multipart (files)
    const ENCTYPE_STR_MULTIPART  = 'multipart/form-data';
	
	//! Encoding type string for urlencoded
    const ENCTYPE_STR_URLENCODED = 'application/x-www-form-urlencoded';
    
    /**
     * Construct a new HTML Rendable form
     * @param array $options Acceptable options are
     * 	- action (default: ''): The url that form will post to.
     *  - method : post,
     *  - nobrowservalidation: (default false) : Skip browser auto validation. 
     *  - buttons : An associative array with buttons of this form.
     *    Each entry will support:
     *    	- label (default: button id): The display of this button
     *      - type (default: 'submit'): Available values are button, submit, reset
     *      - attribs : Extra html attributes
     *      .
     *  - attribs: Extra html attributes to be set at the main div.
     * .
     */
	public function __construct($options)
	{
		parent::__construct();
		$this->options->extend($options,
			array(
				'action' => '',
				'nobrowservalidation' => false,
				'buttons' => array(
					'submit' => array('label' => 'Submit'),
					'reset' => array('label' => 'Reset', 'type' => 'reset')
				),
				'method' => 'post',
				'attribs' => array()
			));
		
		foreach($this->options['buttons'] as $bid => $but) {
			$this->options['buttons'][$bid] = array_merge(array(
					'label' => $bid,
					'type' => 'submit',
					'attribs' => array()
				),$this->options['buttons'][$bid]);
		}
	}
	
	/**
	 * Render this form and return the element with whole form.
	 * @return Output_HTMLTag
	 */
	public function render()
	{
		if ($this->getResultCode() == self::RESULT_NOTPROCESSED)
			$this->process();
			
		$main_el = tag('div', $this->options['attribs'],
			tag('span class="title"', $this->options['title']));
		$form_el = tag('form', array(
				'action' => $this->options['action'],
				'method' => $this->options['method'],
				'enctype' => $this->getEncodingTypeString()))
			->appendTo($main_el)->push_parent();
		if ($this->options['nobrowservalidation'])
			$form_el->attr('novalidate', 'novalidate');
			
		// Render fields
		etag('ul class="fields"')->push_parent();
		$this->walkFields(function($field){
			etag('li')->attr('data-name', $field->getName())->push_parent();
			
			etag('label', $field->options['label']);
			
			// Render field
			Output_HTMLTag::get_current_parent()->append($field->render());
			
			// Add extra fields
			if (($field->isValid() === false) && ($field->getError()))
				etag('span class="ui-form-error"', (string)$field->getError());
			else if ($field->options->has('hint'))
				etag('span class="ui-form-hint"', $field->options['hint']);
			Output_HTMLTag::pop_parent();
		});
		Output_HTMLTag::pop_parent();
		
		// Render buttons
	    etag('div class="buttons"')->push_parent();
        foreach($this->options['buttons'] as $but_id => $but_parm)
        {
            $but_parm['attribs']['name'] = $but_id;
        	$but_parm['attribs']['value'] = $but_parm['label'];
        	$but_parm['attribs']['type'] = $but_parm['type'];

			etag('input', $but_parm['attribs']);
        }
		
		Output_HTMLTag::pop_parent(2);
		return $main_el;
	}
	
	/**
	 * Get the encoding type string
	 */
	public function getEncodingTypeString()
	{
		if ($this->getEncodingType() == Form_Field_Interface::ENCTYPE_MULTIPART)
			return self::ENCTYPE_STR_MULTIPART;
		else
			return self::ENCTYPE_STR_URLENCODED;
	}
}