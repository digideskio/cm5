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
		if (!isset($_FILES[$this->getName()]))
			return $this->value = null;
			
		// Upload of multiple files.
		if ($this->options['multiple']) {
			$values = array();
			foreach($_FILES[$this->getName()]['name'] as $idx => $name) {
				if ($_FILES[$this->getName()]['error'][$idx] == UPLOAD_ERR_NO_FILE)
					continue;
					
				 $uploadedfile = new UploadedFile(
					$_FILES[$this->getName()]['name'][$idx],
					$_FILES[$this->getName()]['type'][$idx],
					$_FILES[$this->getName()]['tmp_name'][$idx],
					$_FILES[$this->getName()]['size'][$idx],
					$_FILES[$this->getName()]['error'][$idx]
				);
				
				$values[] = $uploadedfile;
			}
			
			return $this->value = $values;
		}
			
		
		// Simple upload		
		if ($_FILES['error'] == UPLOAD_ERR_NO_FILE)
			return $this->value = null;
			
		return $this->value = 
			new UploadedFile(
				$_FILES[$this->getName()]['name'],
				$_FILES[$this->getName()]['type'],
				$_FILES[$this->getName()]['tmp_name'],
				$_FILES[$this->getName()]['size'],
				$_FILES[$this->getName()]['error']
			);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Form_Field_Input::render()
	 * @return Output_HTMLTag html element.
	 */
	public function render()
	{
		$t = tag('input type="file"', $this->options['attribs']);
		
		if ($this->options->get('multiple')) {
			$t->attr('name', $this->getName() .'[]');
			$t->attr('multiple', 'multiple');
		} else {
			$t->attr('name', $this->getName());
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