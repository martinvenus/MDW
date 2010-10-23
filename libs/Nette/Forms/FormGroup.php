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
 * A user group of form controls.
 *
 * @author     David Grudl
 *
 * @property-read array $controls
 * @property-read array $options
 */
class NFormGroup extends NObject
{
	/** @var SplObjectStorage */
	protected $controls;

	/** @var array user options */
	private $options = array();



	public function __construct()
	{
		$this->controls = new SplObjectStorage;
	}



	/**
	 * @return NFormGroup  provides a fluent interface
	 */
	public function add()
	{
		foreach (func_get_args() as $num => $item) {
			if ($item instanceof IFormControl) {
				$this->controls->attach($item);

			} elseif ($item instanceof Traversable || is_array($item)) {
				foreach ($item as $control) {
					$this->controls->attach($control);
				}

			} else {
				throw new InvalidArgumentException("Only IFormControl items are allowed, the #$num parameter is invalid.");
			}
		}
		return $this;
	}



	/**
	 * @return array IFormControl
	 */
	public function getControls()
	{
		return iterator_to_array($this->controls);
	}



	/**
	 * Sets user-specific option.
	 * Options recognized by NConventionalRenderer
	 * - 'label' - textual or NHtml object label
	 * - 'visual' - indicates visual group
	 * - 'container' - container as NHtml object
	 * - 'description' - textual or NHtml object description
	 * - 'embedNext' - describes how render next group
	 * @param  string key
	 * @param  mixed  value
	 * @return NFormGroup  provides a fluent interface
	 */
	public function setOption($key, $value)
	{
		if ($value === NULL) {
			unset($this->options[$key]);

		} else {
			$this->options[$key] = $value;
		}
		return $this;
	}



	/**
	 * Returns user-specific option.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	final public function getOption($key, $default = NULL)
	{
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}



	/**
	 * Returns user-specific options.
	 * @return array
	 */
	final public function getOptions()
	{
		return $this->options;
	}

}
