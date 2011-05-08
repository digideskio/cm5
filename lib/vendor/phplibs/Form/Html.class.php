<?php

/**
 * HTML Rendered for forms.
 */
class Form_Html extends Form
{
	//! Encoding type for urlencoded
    const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
    
    //! Encoding type for multipart (files)
    const ENCTYPE_MULTIPART  = 'multipart/form-data';
    
    /**
     * Construct a new HTML Rendable form
     * @param array $options Acceptable options are
     * 	- action (default: ''): The url that form will post to.
     *  - enctype : The encoding type of this form.
     *  - method : post,
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
				'enctype' => self::ENCTYPE_URLENCODED,
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
		tag('form', array('action' => $this->options['action'], 'method' => $this->options['method']))
			->appendTo($main_el)->push_parent();
		
		// Render fields
		etag('ul class="fields"')->push_parent();
		foreach($this->fields as $field) {
			etag('li')->attr('data-name', $field->getName())->push_parent();
			
			etag('label', $field->options['label']);
			
			// Render field
			Output_HTMLTag::get_current_parent()->append($field->render());
			
			// Add extra fields
			if (($field->isValid() === false) && ($field->options->has('onerror')))
				etag('span class="ui-form-error"', $field->options['onerror']);
			else if ($field->options->has('hint'))
				etag('span class="ui-form-hint"', $field->options['hint']);
			Output_HTMLTag::pop_parent();
		}
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
}