<?php


class CM5_Module_ContantForm_Form extends Form_Html{
	
	private $target_email;
	
	private $form_id;
	
	public function __construct($form_id, $target_email, $title, $button_label, $fields)
	{
		$this->target_email = $target_email;
		$this->form_id = $form_id;
		$this->title = $title;
		
		// Create the form
		parent::__construct(null, array(
			'action' => '#contact_form_' . $form_id,
			'title' => $title,
			'nobrowservalidation' => false,
			'attribs' => array(
				'id' => 'contact_form_' . $form_id,
				'class' => 'form contact',
			),
			'buttons' => array(
				'send' => array('label' => $button_label)
			)
		));
		
		// Add fields
		$this->add(field_hidden('contact_form_id', array('label' => '', 'value' => $this->form_id)));
		foreach($fields as $field) {
			$fopts = $field['options'];
			$fopts['type'] = isset($fopts['type'])?$fopts['type']:'text';
			$this->add(call_user_func('field_' . $fopts['type'], $field['name'], $fopts));
		}
	}
	
	public function getFormId()
	{
		return $this->form_id;
	}
	
	public function getTargetEmail()
	{
		return $this->target_email;
	}
	
	public function process($submitted = null)
	{
		// skip if it is not ours
		if (!isset($_POST['contact_form_id']) || ($_POST['contact_form_id'] != $this->form_id)) {
			return $this->result_code = Form::RESULT_NOPOST;
		}
		
		return parent::process($submitted);
	}
	
	public function onProcessValid()
	{
		$values = $this->getValues();
		$mail = new Zend_Mail('UTF-8');
		$mail->setSubject(CM5_Config::getInstance()->site->title . ' | Contact Form [' . $this->title . ']');
		$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
		$mail->addTo($this->target_email, $this->target_email);
		$body = '';
		foreach($values as $name => $value) {
			if (in_array($name, array('contact_form_id')))
				continue;
			$title = ($this->get($name) instanceof Form_Field)
				?$this->get($name)->options['label']
				:$name;
			$body .= "-| " . $title . " |-------------------------------------\n" . 
				(is_array($value)?$value[0]:$value) . "\n\n"; 
		}
		$mail->setBodyText($body);
		CM5_Mailer::getInstance()->send($mail);
	}
}
