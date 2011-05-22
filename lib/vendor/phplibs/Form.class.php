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

	//! Encoding type string for multipart (files)
    const ENCTYPE_STR_MULTIPART  = 'multipart/form-data';
	
	//! Encoding type string for urlencoded
    const ENCTYPE_STR_URLENCODED = 'application/x-www-form-urlencoded';
    
    /**
     * Name of this form
     * @var string
     */
    protected $name = null;
    
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
	protected $result_code = self::RESULT_NOTPROCESSED;
	
	/**
	 * Array with all event dispatchers for forms.
	 * @var array
	 */
	private static $event_dispatchers = array();
	
	/**
	 * Get the event dispatcher corresponding to the call class.
	 * Available events are:
	 * 	- initialized: Notify a new form object is initialized.
	 *  - process.get: Notify that a GET was successfully processed.
	 *  - process.post: Notify that a POST was successfully processed
	 *  - process.valid: Notify that a POST is processed with valid result.
	 *  - process.invalid: Notify that a POST is processed with invalid result.
	 *  - processed: Notify that process was finished.
	 * @return EventDispatcher Object with events for the form
	 * 	class that was called from.
	 * @note You can also overide the equivalent function if you have
	 *  subclassed the class. e.g. onInitialized(), onProcessGet() ...
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
	public function __construct($name, $options = array())
	{
		$this->name = $name;
		$this->options = new Options($options,
			array('enctype' => null));

		if (method_exists($this, 'configure'))
			$this->configure();
			
		$this->notifyEvent('initialized', 'onInitialized');
	}
	
	/**
	 * Get the name of the form
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Change the name of the form
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
	
	/**
	 * Get the result code after processing.
	 */
	public function getResultCode()
	{
		return $this->result_code;
	}
	
	/**
	 * Check if this form is processed.
	 */
	public function isProcessed()
	{		
		return $this->result_code == self::RESULT_NOTPROCESSED;
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
		
		if (isset(self::$event_dispatchers[get_called_class()]))
			static::events()->notify($name,
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

		$fix_files_keys = function($files) use (& $fix_files_keys)
		{
			if (isset($files['name'], $files['tmp_name'], $files['size'], $files['type'], $files['error'])){
				
				// Multiple values for post-keys indexes
				$move_indexes_right = function($files) use(& $move_indexes_right)
				{
					if (!is_array($files['name']))
						return $files;
					$results = array();
					foreach($files['name'] as $index => $name) {
						$reordered = array(
							'name' => $files['name'][$index],
							'tmp_name' => $files['tmp_name'][$index],
							'size' => $files['size'][$index],
							'type' => $files['type'][$index],
							'error' => $files['error'][$index],
						);
						
						// If this is not leaf do it recursivly
						if (is_array($name))
							$reordered = $move_indexes_right($reordered);
						
						$results[$index] = $reordered;
					}
					return $results;
				};
				return $move_indexes_right($files);
			}
			
			// Re order pre-keys indexes			
			array_walk($files, function(&$sub) use(& $fix_files_keys) {
				$sub = $fix_files_keys($sub);
			});			
			return $files;
		};
		
		
		
		if ($submitted == null){
			$submitted = ($_SERVER['REQUEST_METHOD'] == 'POST') 
				? (count($_FILES))
					? array_merge_recursive($_POST, $fix_files_keys($_FILES))
					: $_POST 
				: null;
		}

		// Check if the form is posted
	    if ($submitted == null) {	    	
	    	$this->result_code = self::RESULT_NOPOST;
	    	$this->notifyEvent('process.get', 'onProcessGet');
	    	
	    	$this->notifyEvent('processed', 'onProcessed');
	    	return $this->result_code;
	    }
	    
	    // Process each field
	    parent::process($submitted);	    
	    
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