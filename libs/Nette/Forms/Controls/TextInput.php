<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Forms
 */



/**
 * Single line text input control.
 *
 * @author     David Grudl
 */
class NTextInput extends NTextBase
{

	/**
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 */
	public function __construct($label = NULL, $cols = NULL, $maxLength = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'text';
		$this->control->size = $cols;
		$this->control->maxlength = $maxLength;
		$this->filters[] = callback($this, 'sanitize');
		$this->value = '';
	}



	/**
	 * Filter: removes unnecessary whitespace and shortens value to control's max length.
	 * @return string
	 */
	public function sanitize($value)
	{
		if ($this->control->maxlength && iconv_strlen($value, 'UTF-8') > $this->control->maxlength) {
			$value = iconv_substr($value, 0, $this->control->maxlength, 'UTF-8');
		}
		return NString::trim(strtr($value, "\r\n", '  '));
	}



	/**
	 * Sets or unsets the password mode.
	 * @param  bool
	 * @return NTextInput  provides a fluent interface
	 */
	public function setPasswordMode($mode = TRUE)
	{
		$this->control->type = $mode ? 'password' : 'text';
		return $this;
	}



	/**
	 * Generates control's HTML element.
	 * @return NHtml
	 */
	public function getControl()
	{
		$control = parent::getControl();
		if ($this->control->type !== 'password') {
			$control->value = $this->getValue() === '' ? $this->translate($this->emptyValue) : $this->value;
		}
		return $control;
	}



	public function notifyRule(NRule $rule)
	{
		if (is_string($rule->operation) && strcasecmp($rule->operation, ':length') === 0 && !$rule->isNegative) {
			$this->control->maxlength = is_array($rule->arg) ? $rule->arg[1] : $rule->arg;

		} elseif (is_string($rule->operation) && strcasecmp($rule->operation, ':maxLength') === 0 && !$rule->isNegative) {
			$this->control->maxlength = $rule->arg;
		}

		parent::notifyRule($rule);
	}


}
