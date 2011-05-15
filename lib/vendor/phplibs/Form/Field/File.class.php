<?php

require_once(__DIR__ . '/Html.class.php');

/**
 * HTML5 Implemenetation of input[type=file] field.
 */
class Form_Field_File extends Form_Field_Input
{
	/**
	 * Construct a new file field.
	 * @see field_file() for shortcut
	 * @see Form_Field_Input for more options.
	 * @param string $name The name of the field
	 * @param array $options Available options are:
	 * 	- multiple (Default false) : If true and the browser supports it, field
	 * 		can accept and manage multiple files at once.
	 *  - required (Default false) : If true then this field is required.
	 *  .
	 */
	public function __construct($name, $options = array())
	{
		parent::__construct($name, array_merge(
				array('type' => 'file', 'enctype' => Form_Field_Interface::ENCTYPE_MULTIPART),
				$options));
	}
	
	protected function onParse($submitted)
	{
		// Dont change immutable
		if (!$this->isMutable())
			return $this->getValue();
		
		// Check for existing value
		if (!isset($submitted[$this->getName()]))
			return null;
			
		// Upload of multiple files.
		if ($this->options->get('multiple')) {
			$values = array();
			foreach($submitted[$this->getName()] as $idx => $file) {
				if ($file['error'] == UPLOAD_ERR_NO_FILE)
					continue;
					
				 $uploadedfile = new UploadedFile(
					$file['name'],
					$file['type'],
					$file['tmp_name'],
					$file['size'],
					$file['error']
				);
				
				$values[] = $uploadedfile;
			}
			
			return $values;
		}
			
		
		// Simple upload		
		if ($submitted[$this->getName()]['error'] == UPLOAD_ERR_NO_FILE)
			return null;
			
		return new UploadedFile(
				$submitted[$this->getName()]['name'],
				$submitted[$this->getName()]['type'],
				$submitted[$this->getName()]['tmp_name'],
				$submitted[$this->getName()]['size'],
				$submitted[$this->getName()]['error']
			);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Form_Field_Input::render()
	 * @return Output_HTMLTag html element.
	 */
	public function render($namespace)
	{
		$t = tag('input type="file"', $this->generateAttributes());
		
		if ($this->options->get('multiple')) {
			$t->attr('name', $this->getHtmlFullName($namespace) .'[]');
			$t->attr('multiple', 'multiple');
		} else {
			$t->attr('name', $this->getHtmlFullName($namespace));
		}
		return $t;
	}
}

/**
 * Create a Form_Field_File
 * @see Form_Field_File
 * @return Form_Field_File
 */
function field_file($name, $options = array()) {
	return new Form_Field_File($name, array_merge($options));
}