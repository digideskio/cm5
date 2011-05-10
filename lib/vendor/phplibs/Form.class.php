<?php

require_once(__DIR__ . '/Form/Field/Container.class.php');

/**
 * Complete implementation for Form processing.
 * Use this class by instantiating, adding fields with addField
 * and call process() 
 */
class Form extends Form_Field_Container
{
	//! Result code for unprocessed status
	const RESULT_NOTPROCESSED = -1;
	
	//! When processed but no data was submitted.
	const RESULT_NOPOST = 0;
	
	//! When processed and submitted data were valid.
	const RESULT_VALID = 1;
	
	//! When processed and submitted data were not valid.
	const RESULT_INVALID = 2;

	/**
	 * Form options.
	 * @var Options 
	 */
	public $options = array();
	
	/**
	 * The result code of this form
	 * @var integer
	 * @see getResultCode()
	 */
	private $result_code = self::RESULT_NOTPROCESSED;
	
	/**
	 * Array with all event dispatchers for forms.
	 * @var array
	 */
	private static $event_dispatchers = array();
	
	/**
	 * Get the event dispatcher corresponding to the call class.
	 * Available events are:
	 * 	- initialized: When a new form object is initialized.
	 *  - process.get: When a GET is successfully processed
	 *  - process.post: When a POST is successfully processed
	 *  - process.valid: When a POST is processed and there was a valid result.
	 *  - process.invalid: When a POST is processed and there was a invalid result.
	 * @return EventDispatcher Object with events for the form
	 * 	class that was called from.
	 */
	public static function events()
	{
		$class_name = get_called_class();
		if (isset(self::$event_dispatchers[$class_name]))
			return self::$event_dispatchers[$class_name];
		return self::$event_dispatchers[$class_name] = new EventDispatcher(array(
			'initialized',
			'processed',
			'process.get',
			'process.post',
			'process.valid',
			'process.invalid'
		));
	}
	
	/**
	 * Construct a new Form object
	 * @param array $options A list of options for this form.
     *  - enctype (default : null): The encoding type of this form.
     *  	This field overides detection based on contained fields
	 */
	public function __construct($options = array())
	{
		$this->options = new Options($options,
			array('enctype' => null));
	}
	
	/**
	 * Get the result code after processing.
	 */
	public function getResultCode()
	{
		return $this->result_code;
	}
	
	/**
	 * Check if this form is valid.
	 */
	public function isValid()
	{		
		return $this->result_code == self::RESULT_VALID;
	}
	
	/**
	 * Notify all event listeners for this event
	 * @param string $name The name of the event.
	 * @param string $method_name The name of the object function.
	 * @param string $extra_arguments Extra arguments for the event listerens.
	 */
	private function notifyEvent($name, $method_name, $extra_arguments = array())
	{
		if (method_exists($this, $method_name))
			call_user_func_array(array($this, $method_name), $extra_arguments);
		static::events()->notify('process.get',
			array_merge(array('form' => $this), $extra_arguments));
	}
	
	/**
	 * Actual process the submitted data
	 * @param array $submitted An array with all values submitted
	 * 	for form processing. If this value is null then the $_POST
	 * 	is checked.
	 * @return The result code of the process.
	 * @see getResultCode()
	 */
	public function process($submitted = null)
	{	
		if ($submitted == null){
			$submitted = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : null;
		}

		// Check if the form is posted
	    if ($submitted == null) {	    	
	    	$this->result_code = self::RESULT_NOPOST;
	    	$this->notifyEvent('process.get', 'onProcessGet');
	    	
	    	$this->notifyEvent('processed', 'onProcessed');
	    	return $this->result_code;
	    }
	    
	    // Process each field
	    $this->walkFields(function($field) use($submitted){	    
	       	$field->process($submitted);
	    });
	    
	    // Send process.post event.
	    $this->notifyEvent('process.post', 'onProcessPost');	   	

		// Set result code
	   	if (parent::isValid()) {
	   		$this->result_code = self::RESULT_VALID;	   		
	   		$this->notifyEvent('process.valid', 'onProcessValid');
	   	} else {
	   		$this->result_code = self::RESULT_INVALID;
	   		$this->notifyEvent('process.invalid', 'onProcessInvalid');
	   	}
	   	
	   	$this->notifyEvent('processed', 'onProcessed');
    	return $this->result_code;
	}
}