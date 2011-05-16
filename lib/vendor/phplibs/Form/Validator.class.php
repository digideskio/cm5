<?php

/**
 * Collection of default validators.
 */
class Form_Validator
{

	/**
	 * Always return invalid.
	 * @param string $error The error message in case of failure.
	 */
	public static function invalid($error = 'This field is invalid.')
	{
		return function($value, & $result_error) use($error){
			$result_error = $error;
			return false;
		};
	}
	
	/**
	 * Always return valid.
	 */
	public static function valid()
	{
		return function($value){
			return true;
		};
	}
	
	/**
	 * Return the boolean AND between other validators.
	 * @param callable $validator1
	 * @param callable $validator2
	 * @param ...  
	 */
	public static function boolAnd()
	{
		$validators = func_get_args();		
		if (empty($validators))
			return self::invalid();
		
		return function($value, & $error, $field) use($validators) {
			foreach($validators as $v)				
				if (!$v($value, $error, $field))
					return false;
			return true;
		};
	}
	
	/**
	 * Return the boolean OR between other validators.
	 * @param callable $validator1
	 * @param callable $validator2
	 * @param ...  
	 */
	public static function boolOr()
	{
		$validators = func_get_args();		
		if (empty($validators))
			return self::invalid();
		
		return function($value, & $error, $field) use($validators) {
			$last_error = 0;
			foreach($validators as $v) {
				if ($v($value, $last_error, $field))
					return true;
			}
			$error = $last_error;
			return false;
		};
	}
	
	/**
	 * Return the opposite result of another validator.
	 * @param callable $validator The validator that the result will be swifted.
	 * @param string $error_override If not null, this message will override original
	 *  validators error message.
	 */
	public static function boolNot($validator, $error_override = null)
	{
		return function($value, & $result_error, $field) use($validator, $error_override) {
			if ($error_override === null)
				return !$validator($value, $result_error, $field);

			if ($validator($value)) {
				$result_error = $error_override;
				return false;
			}
			return true;
		};
	}
	
	/**
	 * Validate based on a regular expression
	 * @param string $pattern The preg expression to test.
	 * @param string $error The error message in case of failure.
	 */
	public static function matchRegex($pattern, $error = "Does not match field criteria.")
	{
		return function($value, & $result_error) use($pattern, $error) {
			if (preg_match($pattern, $value) > 0)
				return true;
			$result_error = $error;
			return false;
		};		
	}
	
	/**
	 * Valid only if strlength is in the limits of minimum and maximum
	 * @param $min The minimum size that value must be equal or greater.
	 * 	If you give NULL no checks will be done for minimum size.
	 * @param $max The maximum size that value must be equal or greater.
	 * 	If you give NULL no checks will be done for maximum size.
	 * @param string $error The error message in case of failure.
	 */
	public static function isStrlenBetween($min, $max, $error = "String has exceeded acceptable limits.")
	{
		return function($value, & $result_code) use($min, $max, $error) {
			$size = strlen($value);
			if ($min !== null)
				if ($size < $min) {
					$result_code = $error;
					return false;
				}
			if ($max !== null)
				if ($size > $max) {
					$result_code = $error;
					return false;					
				}
			return true;
		};
	}
	
	/**
	 * Valid only if number is in the limits of minimum and maximum
	 * @param $min The minimum must be equal or greater.
	 * 	If you give NULL no checks will be done for lower bound.
	 * @param $max The maximum size that value must be equal or greater.
	 * 	If you give NULL no checks will be done for uper bound.
	 * @param string $error The error message in case of failure.
	 */
	public static function isNumberBetween($min, $max, $error = "Number has exceeded acceptable values.")
	{
		return function($value, & $result_code) use($min, $max, $error) {
			
			if ($min !== null)
				if ($value < $min) {
					$result_code = $error;
					return false;
				}
			if ($max !== null)
				if ($value > $max) {
					$result_code = $error;
					return false;					
				}
			return true;
		};
	}
	
	/**
	 * Check if a number is properly quantized in steps
	 */
	public static function isNumberQuantized($step, $base = null, $error = "Only certain numbers are allowed.")
	{
		return function($value, & $result_code) use($step, $base, $error) {			
			
			if ($base != null)
				$value -= $base;
			if ($result = (fmod($value, $step) == 0))
				return true;
			$result_code = $error;
			return false;			
		};
	}
		
	/**
	 * Validate based on equallity with another subject.
	 * @param mixed $subject the subject to be compared at.
	 * @param string $error The error message in case of failure.
	 * @param boolean $strict Flag if it will use strict comparison.
	 */
	public static function isEqual($subject, $error = "Does not match field criteria.", $strict = false)
	{
		return function($value, & $result_error) use($subject, $error, $strict) {
			
			$result = ($strict) ? $value === $subject : $value == $subject;
			
			if (!$result)
				$result_error = $error;
			return $result;
		};
	}
	
	/**
	 * Validate only if this is a number
	 * @param string $error The error message in case of failure.
	 */
	public static function isNumber($error = "This is not a number.")
	{
		return function($value, & $result_error) use($subject, $error) {
			if (!is_numeric($value)) {
				$result_error = $error;
				return false;
			}
			return true;
		};
	}
	
	/**
	 * Valid only if field is empty.
	 * @param string $error The error message in case of failure.
	 */
	public static function isEmpty($error = "This field must be empty.")
	{
		return function($value, & $result_error) use($error){
			if (!empty($value)) {
				$result_error = $error;
				return false;
			}
			return true;
		};
	}
	
	/**
	 * Valid only if it is a valid url.
	 * @param string $error The error message in case of failure.
	 */
	public static function isUrl($error = "This is not valid url address.")
	{
		return self::matchRegex('#^((http|https|ftp)://([\w-\d]+\.)+[\w-\d]+)(/[\w~,;\-\./?%&+\#=]*)?$#', $error);		
	}

	/**
	 * Valid only if it is a valid simple color.
	 * @param string $error The error message in case of failure.
	 */
	public static function isSimpleColor($error = "This is not valid color.")
	{
		return self::matchRegex('/^#[a-zA-Z0-9]{6}$/', $error);		
	}
	
	/**
	 * Valid only if it is a valid email.
	 * @param string $error The error message in case of failure.
	 */
	public static function isEmail($error = "This is not valid email address.")
	{
		return self::matchRegex('/^([a-zA-Z0-9\-\.])+@([a-zA-Z0-9\-\.])+$/', $error);		
	}
	
	
	/**
	 * Valid only if field is not empty.
	 * @param $error The error message to display on failure.
	 */
	public static function isNotEmpty($error = "This field is required.")
	{
		return function($value, & $result_error) use ($error){			
			if (empty($value)) {
				$result_error = $error;
				return false;
			}
			return true;
		};
	}
	
	/**
	 * Valid only if element is checked.
	 * @param $error The error message to display on error.
	 */
	public static function isChecked($error = 'This field must be checked.')
	{
		return function ($value, & $result_error, $field) use ($error) {
			if (!$field->isChecked()) {
				$result_error = $error;
				return false;
			}
			return true;
		};
	}
	
	/**
	 * Validate all elements of in array of value
	 * @param callable $validator The validator to be used to validate each element
	 */
	public static function eachElement($validator)
	{
		return function($values, & $result_error, $field) use($validator) {
			if (!is_array($values))
				return false;
			foreach($values as $value)
				if (!$validator($value, $result_error, $field))
					return false;
			return true;
			exit;
		};
	}
	
	/**
	 * Valid only if field has a value in the $subject_array.	
	 * @param $error The error message to display on error. 
	 */
	public static function inArray($subject_array, $error = 'This is not an acceptable option.')
	{
		return function($value, & $result_error, $field) use($subject_array, $error) {
			if (! in_array($value, $subject_array)) {
				$result_error = $error;
				return false;
			}
			return true;
		};
	}
}