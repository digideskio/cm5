<?php

/**
 * Options container class. It is helpfull to automate
 * the procedure of managing the options of any object. It
 * supports default values, mandatory values and extending.
 */
class Options extends ArrayObject
{
	/**
	 * Construct a new options container and initialize it.
	 * @param mixed $values Directly assigned options on this container, or another Options.
	 * @param array $default Default values for not set keys.
	 * @param array $mandatory An array with the names of values that are mandatatory.
	 * @throws RuntimeException If a mandatory field was not set.
	 */
	public function __construct($values, $default = array(), $mandatory = array())
	{		
		parent::__construct($this->calculateValues($values, $default, $mandatory));
	}
	
	/**
	 * Function to check for mandatory and create the array merging result
	 * @see __construct()
	 * @return array With the calculate result
	 * @throws RuntimeException
	 */
	private function calculateValues($values, $default, $mandatory)
	{
		if (is_array($values))
			$nvalues = array_merge($default, $values);
		elseif ($values instanceof ArrayObject)
			$nvalues = array_merge($default, $values->getArrayCopy());
			
		if (count($mandatory)) {
			$missing = array_diff($mandatory, array_keys($nvalues));
			if (count($missing) > 0)
				throw new RuntimeException("Missing mandatory options from Options object.");
    	}
    	return $nvalues;
	}
	
	/**
	 * Check if an option is set.
	 * @param string $name The key name of the option
	 */
	public function has($name)
    {
        return $this->offsetExists($name);
    }
    
    /**
     * Get the value of an option.
     * @param string $name The key name of the option.
     * @return mixed The value of option or null if it was not found. 
     */
    public function get($name)
    {
        return $this->has($name)?$this->offsetGet($name):null;
    }
    
    /**
     * Add or overwrite the value of an option.
     * @param string $name The name of the option.
     * @param string $value The value of the option.
     */
    public function set($name, $value)
    {
        $this->offsetSet($name, $value);
    }
    
    /**
     * Add only if value does NOT exists.
     * @param string $name The name of the option.
     * @param string $value The value of the option.
     */
    public function add($name, $value)
    {
    	if (!$this->offsetExists($name))
        	$this->offsetSet($name, $value);
    }
    
    /**
     * Remove an option from the container.
     * @param string $name The name of the option.
     * @param string $value The value of the option.
     */
    public function remove($name)
    {
    	if ($this->offsetExists($name))
        	$this->offsetUnset($name);
    }
    
    /**
	 * Extend current install with more assigned options
	 * @param mixed $values Directly assigned options on this container, or another Options.
	 * @param array $default Default values for not set keys.
	 * @param array $mandatory An array with the names of values that are mandatatory.
	 * @throws RuntimeException If a mandatory field was not set.
	 */
    public function extend($values, $default = array(), $mandatory = array())
    {    	
		$this->exchangeArray($this->calculateValues($values, $default, $mandatory));
    }
    
}