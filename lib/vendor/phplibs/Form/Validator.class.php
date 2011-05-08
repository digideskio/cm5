<?php

/**
 * Collection of default validators.
 */
class Form_Validator
{

	/**
	 * Always return invalid.
	 */
	public static function invalid()
	{
		return function($field){
			return false;
		};
	}
	
	/**
	 * Always return valid.
	 */
	public static function valid()
	{
		return function($field){
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
		
		return function($field, & $error) use($validators) {
			foreach($validators as $v)
				if (!$v($field, $error))
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
		
		return function($field) use($validators) {
			foreach($validators as $v)
				if ($v($field))
					return true;
			return false;
		};
	}
	
	/**
	 * Return the opposite result of another validator.
	 * @param callable $validator The validator that the result will be swifted.
	 */
	public static function boolNot($validator)
	{
		return function($field) use($validator) {
			return !$validator($field);
		};
	}
	
	/**
	 * Validate based on a regular expression
	 */
	public static function regCheck($pattern)
	{
		return function($field) use($pattern) {
			return (preg_match($pattern, $field->getValue()) > 0);
		};		
	}
	
	/**
	 * Valid only if in the limits of minimum and maximum
	 * @param $min The minimum size that value must be equal or greater.
	 * 	If you give NULL no checks will be done for minimum size.
	 * @param $max The maximum size that value must be equal or greater.
	 * 	If you give NULL no checks will be done for maximum size.
	 */
	public static function strSize($min, $max)
	{
		return function($field) use($min, $max) {
			$size = strlen($field->getValue());
			if ($min !== null)
				if ($size < $min)
					return false;
			if ($max !== null)
				if ($size > $max)
					return false;
			return true;
		};
	}
		
	/**
	 * Validate based on equallity with another subject.
	 * @param mixed $subject the subject to be compared at.
	 * @param boolean $strict Flag if it will use strict comparison.
	 */
	public static function isEqual($subject, $strict = false)
	{
		return function($field) use($subject, $strict) {
			if ($strict)
				return $field->getValue() === $subject;
			else
				return $field->getValue() == $subject;
		};
	}
	
	/**
	 * Valid only if field is empty.	 
	 */
	public static function isEmpty()
	{
		return function($field) {
			$value = $field->getValue();
			return empty($value);
		};
	}
	
	/**
	 * Valid only if field is not empty.	 
	 */
	public static function isNotEmpty()
	{
		return function($field) {
			$value = $field->getValue();
			return ! empty($value);
		};
	}
	
	/**
	 * Valid only if field has a value in the $subject_array.	 
	 */
	public static function inArray($subject_array)
	{
		return function($field) use($subject_array) {
			return in_array($field->getValue(), $subject_array);
		};
	}
}