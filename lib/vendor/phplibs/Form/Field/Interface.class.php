<?php

/**
 * Interface that all fields must follow.
 */
interface Form_Field_Interface 
{
    //! Code when the selected encoding type "multipart"
    const ENCTYPE_MULTIPART = 2;
    
    //! Code when the selected encoding type "urlencoded"
    const ENCTYPE_URLENCODED = 1;
    
    //! Code when the selected encoding type is "Auto" (meaning this has no prerequisite)
    const ENCTYPE_AUTO = 0;
    
    /**
     * When requesting to process submitted data.
     * @param array $submitted
     */
	public function process($submitted);
	
	/**
	 * When requested to get the name of this field.
	 * @return string The name of the field
	 */
	public function getName();
	
	/**
	 * When requested to get the value of this field.
	 * @return mixed
	 */
	public function getValue();
	
	/**
	 * Asking if this field is valid or not after
	 * processing data.
	 */
	public function isValid();
	
	/**
	 * Get the error message in case of failed validation.
	 */
	public function getError();
	
	/**
	 * Get the desired encoding type.
	 */
	public function getEncodingType();
}