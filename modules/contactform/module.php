<?php

require_once __DIR__ . '/lib/Form.class.php';

class CM5_Module_ContantForm extends CM5_Module
{
	public function onInitialize()
	{
		CM5_Core::getInstance()->events()->connect('page.pre-render', array($this, 'onPagePreRender'));
	}
	
	public function onPagePreRender($event)
	{
		$page = $event->filtered_value;
		
		// Fast check to skip using regular expressions on all pages.
		if (strstr($page->body, '##contactform') < 0)
			return;

//		Layout::getActive()->deactivate();
		$offset = 0;
		while(preg_match('/##contactform.*?##/s', (string)$page->body,
			$form_match,
			PREG_OFFSET_CAPTURE, $offset)){ 
				
			// Save extracted data
			$replace = array('start' => $form_match[0][1], 'length' => strlen($form_match[0][0]));
			

			// Purify and extract basic information
			$form_text = html_entity_decode(preg_replace('/\<[\s]*br[^\>]*\>/s', '', $form_match[0][0]));
			$form_text = preg_replace('/\n[\s]+/s', "\n", $form_text);
			
			preg_match_all('/##contactform[\s]+(?P<email>[\w_@\.]+)[\s]+\"(?P<title>[^\"]*)\"[\s]+"(?P<button_label>[^\"]*)\"[\s]+'.
				'(?P<fields>.*(?<=[\r\n]))\-[\r\n]+(?P<thank_you>[^#]+)##/s',
				$form_text, $matches);
			$target_email = $matches['email'][0];
			$title = $matches['title'][0];
			$button_label = $matches['button_label'][0];
			$thank_you = rtrim($matches['thank_you'][0]); 
			$fields = $matches['fields'][0];
			
			
			// Analyze fields
			$results = preg_match_all(
				'/(?P<field>(?P<name>[a-zA-Z]+)\((?P<options>[^\)]*)\)([\s]|\<br[^\>]*\>)+)/s',
				$fields,
				$matches);
	
			$fields = array();
			foreach($matches['name'] as $idx => $name) {
				$cleaned_up_options = array();
				foreach(explode(',', $matches['options'][$idx]) as $opt) {
					$chunks = explode('=', $opt);
					$cleaned_up_options[$chunks[0]] = $chunks[1];
				}
				$fields[] = array('name' => $name, 'options' => $cleaned_up_options); 
			}
			// Create a unique form_id based on the position and text.
			$form_id = md5($replace['start'] . substr($page->body, $replace['start'], $replace['length']));
			$form = new CM5_Module_ContantForm_Form($form_id, $target_email, $title, $button_label, $fields);
			
			// Process form
			if ($form->process() == Form::RESULT_VALID) {
				$render = (string)tag('div class="form submitted" html_escape_off', $thank_you)->attr('id', 'contact_form_id_' . $form_id);
			} else {
				$render = (string)$form->render();
			}
			// Render back
			$page->body = substr_replace($page->body, (string)$render, $replace['start'], $replace['length']);
			$offset = $replace['start'] + strlen($render);
			
		}
	
		
	}
	
}
return array(
	'class' => 'CM5_Module_ContantForm',
	'nickname' => 'contactform',
	'title' => 'Contact Form',
	'description' => 'Add contact forms at your pages.'
);