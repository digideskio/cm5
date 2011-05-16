<?php

require_once(__DIR__ . '/Field/Input.class.php');
require_once(__DIR__ . '/Field/Checkable.class.php');
require_once(__DIR__ . '/Field/File.class.php');
require_once(__DIR__ . '/Field/Select.class.php');
require_once(__DIR__ . '/Field/Set.class.php');
require_once(__DIR__ . '/Field/Textarea.class.php');
require_once(__DIR__ . '/Field/Raw.class.php');

/**
 * HTML Rendered for forms.
 */
class Form_Html extends Form
{

    /**
     * Construct a new HTML Rendable form
     * @param $name The name of the form.
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
	public function __construct($name, $options)
	{
		parent::__construct($name);
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
		if ($this->getName())
			$form_el->attr('name', $this->getName());
			
		// Render fields
		etag('ul class="fields"')->push_parent();
		$form = $this;
		$this->walkFields( function($field, $index) use($form) {
			etag('li')->attr('data-name', $field->getName())->push_parent();
			
			// Render field
			Output_HTMLTag::get_current_parent()->append($field->render(array(
				'namespace' => $form->getName(),
				'index' => $index)));
			
			// Add extra fields
			if (($field->isValid() === false) && ($field->getError()))
				etag('span class="error"', (string)$field->getError());
			else if ($field->options->has('hint'))
				etag('span class="hint"', $field->options['hint']);
			Output_HTMLTag::pop_parent();
		} );
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