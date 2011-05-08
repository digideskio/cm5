<?php

require_once(__DIR__ . '/Html.class.php');

class Form_Field_File extends Form_Field_Html
{
	public function render()
	{
		return tag('input type="file" ');
	}
}