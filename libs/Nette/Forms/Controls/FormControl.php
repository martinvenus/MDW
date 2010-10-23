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
 * Base class that implements the basic functionality common to form controls.
 *
 * @author     David Grudl
 *
 * @property-read NForm $form
 * @property-read mixed $control
 * @property-read mixed $label
 * @property-read string $htmlName
 * @property   string $htmlId
 * @property-read array $options
 * @property   ITranslator $translator
 * @property   mixed $value
 * @property-read NHtml $controlPrototype
 * @property-read NHtml $labelPrototype
 * @property-read NRules $rules
 * @property-read array $errors
 * @property   bool $disabled
 * @property   bool $rendered
 * @property   bool $required
*/
abstract class NFormControl extends NComponent implements IFormControl
{
	/** @var string */
	public static $idMask = 'frm%s-%s';

	/** @var string textual caption or label */
	public $caption;

	/** @var mixed unfiltered control value */
	protected $value;

	/** @var NHtml  control element template */
	protected $control;

	/** @var NHtml  label element template */
	protected $label;

	/** @var array */
	private $errors = array();

	/** @var bool */
	private $disabled = FALSE;

	/** @var string */
	private $htmlId;

	/** @var string */
	private $htmlName;

	/** @var NRules */
	private $rules;

	/** @var ITranslator */
	private $translator = TRUE; // means autodetect

	/** @var array user options */
	private $options = array();



	/**
	 * @param  string  caption
	 */
	public function __construct($caption = NULL)
	{
		$this->monitor('NForm');
		parent::__construct();
		$this->control = NHtml::el('input');
		$this->label = NHtml::el('label');
		$this->caption = $caption;
		$this->rules = new NRules($this);
	}



	/**
	 * This method will be called when the component becomes attached to NForm.
	 * @param  IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if (!$this->disabled && $form instanceof NForm && $form->isAnchored() && $form->isSubmitted()) {
			$this->htmlName = NULL;
			$this->loadHttpData();
		}
	}



	/**
	 * Returns form.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return NForm
	 */
	public function getForm($need = TRUE)
	{
		return $this->lookup('NForm', $need);
	}



	/**
	 * Returns name of control within a NForm & INamingContainer scope.
	 * @return string
	 */
	public function getHtmlName()
	{
		if ($this->htmlName === NULL) {
			$s = '';
			$name = $this->getName();
			$obj = $this->lookup('INamingContainer', TRUE);
			while (!($obj instanceof NForm)) {
				$s = "[$name]$s";
				$name = $obj->getName();
				$obj = $obj->lookup('INamingContainer', TRUE);
			}
			$name .= $s;
			if ($name === 'submit') {
				throw new InvalidArgumentException("Form control name 'submit' is not allowed due to JavaScript limitations.");
			}
			$this->htmlName = $name;
		}
		return $this->htmlName;
	}



	/**
	 * Changes control's HTML id.
	 * @param  string new ID, or FALSE or NULL
	 * @return NFormControl  provides a fluent interface
	 */
	public function setHtmlId($id)
	{
		$this->htmlId = $id;
		return $this;
	}



	/**
	 * Returns control's HTML id.
	 * @return string
	 */
	public function getHtmlId()
	{
		if ($this->htmlId === FALSE) {
			return NULL;

		} elseif ($this->htmlId === NULL) {
			$this->htmlId = sprintf(self::$idMask, $this->getForm()->getName(), $this->getHtmlName());
			$this->htmlId = str_replace(array('[]', '[', ']'), array('', '-', ''), $this->htmlId);
		}
		return $this->htmlId;
	}



	/**
	 * Sets user-specific option.
	 * Common options:
	 * - 'rendered' - indicate if method getControl() have been called
	 * - 'required' - indicate if ':required' rule has been applied
	 * - 'description' - textual or NHtml object description (recognized by NConventionalRenderer)
	 * @param  string key
	 * @param  mixed  value
	 * @return NFormControl  provides a fluent interface
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



	/********************* translator ****************d*g**/



	/**
	 * Sets translate adapter.
	 * @param  ITranslator
	 * @return NFormControl  provides a fluent interface
	 */
	public function setTranslator(ITranslator $translator = NULL)
	{
		$this->translator = $translator;
		return $this;
	}



	/**
	 * Returns translate adapter.
	 * @return ITranslator|NULL
	 */
	final public function getTranslator()
	{
		if ($this->translator === TRUE) {
			return $this->getForm(FALSE) ? $this->getForm()->getTranslator() : NULL;
		}
		return $this->translator;
	}



	/**
	 * Returns translated string.
	 * @param  string
	 * @param  int      plural count
	 * @return string
	 */
	public function translate($s, $count = NULL)
	{
		$translator = $this->getTranslator();
		return $translator === NULL || $s == NULL ? $s : $translator->translate($s, $count); // intentionally ==
	}



	/********************* interface IFormControl ****************d*g**/



	/**
	 * Sets control's value.
	 * @param  mixed
	 * @return NFormControl  provides a fluent interface
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}



	/**
	 * Returns control's value.
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}



	/**
	 * Sets control's default value.
	 * @param  mixed
	 * @return NFormControl  provides a fluent interface
	 */
	public function setDefaultValue($value)
	{
		$form = $this->getForm(FALSE);
		if (!$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValue($value);
		}
		return $this;
	}



	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$path = explode('[', strtr(str_replace(array('[]', ']'), '', $this->getHtmlName()), '.', '_'));
		$this->setValue(NArrayTools::get($this->getForm()->getHttpData(), $path));
	}



	/**
	 * Disables or enables control.
	 * @param  bool
	 * @return NFormControl  provides a fluent interface
	 */
	public function setDisabled($value = TRUE)
	{
		$this->disabled = (bool) $value;
		return $this;
	}



	/**
	 * Is control disabled?
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->disabled;
	}



	/********************* rendering ****************d*g**/



	/**
	 * Generates control's HTML element.
	 * @return NHtml
	 */
	public function getControl()
	{
		$this->setOption('rendered', TRUE);
		$control = clone $this->control;
		$control->name = $this->getHtmlName();
		$control->disabled = $this->disabled;
		$control->id = $this->getHtmlId();
		return $control;
	}



	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return NHtml
	 */
	public function getLabel($caption = NULL)
	{
		$label = clone $this->label;
		$label->for = $this->getHtmlId();
		if ($caption !== NULL) {
			$label->setText($this->translate($caption));

		} elseif ($this->caption instanceof NHtml) {
			$label->add($this->caption);

		} else {
			$label->setText($this->translate($this->caption));
		}
		return $label;
	}



	/**
	 * Returns control's HTML element template.
	 * @return NHtml
	 */
	final public function getControlPrototype()
	{
		return $this->control;
	}



	/**
	 * Returns label's HTML element template.
	 * @return NHtml
	 */
	final public function getLabelPrototype()
	{
		return $this->label;
	}



	/**
	 * Sets 'rendered' indicator.
	 * @param  bool
	 * @return NFormControl  provides a fluent interface
	 * @deprecated
	 */
	public function setRendered($value = TRUE)
	{
		$this->setOption('rendered', $value);
		return $this;
	}



	/**
	 * Does method getControl() have been called?
	 * @return bool
	 * @deprecated
	 */
	public function isRendered()
	{
		return !empty($this->options['rendered']);
	}



	/********************* rules ****************d*g**/



	/**
	 * Adds a validation rule.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return NFormControl  provides a fluent interface
	 */
	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		$this->rules->addRule($operation, $message, $arg);
		return $this;
	}



	/**
	 * Adds a validation condition a returns new branch.
	 * @param  mixed     condition type
	 * @param  mixed      optional condition arguments
	 * @return NRules      new branch
	 */
	public function addCondition($operation, $value = NULL)
	{
		return $this->rules->addCondition($operation, $value);
	}



	/**
	 * Adds a validation condition based on another control a returns new branch.
	 * @param  IFormControl form control
	 * @param  mixed      condition type
	 * @param  mixed      optional condition arguments
	 * @return NRules      new branch
	 */
	public function addConditionOn(IFormControl $control, $operation, $value = NULL)
	{
		return $this->rules->addConditionOn($control, $operation, $value);
	}



	/**
	 * @return NRules
	 */
	final public function getRules()
	{
		return $this->rules;
	}



	/**
	 * Makes control mandatory.
	 * @param  string  error message
	 * @return NFormControl  provides a fluent interface
	 * @deprecated
	 */
	final public function setRequired($message = NULL)
	{
		$this->rules->addRule(NForm::FILLED, $message);
		return $this;
	}



	/**
	 * Is control mandatory?
	 * @return bool
	 * @deprecated
	 */
	final public function isRequired()
	{
		return !empty($this->options['required']);
	}



	/**
	 * New rule or condition notification callback.
	 * @param  NRule
	 * @return void
	 */
	public function notifyRule(NRule $rule)
	{
		if (is_string($rule->operation) && strcasecmp($rule->operation, ':filled') === 0) {
			$this->setOption('required', TRUE);
		}
	}



	/********************* validation ****************d*g**/



	/**
	 * Equal validator: are control's value and second parameter equal?
	 * @param  IFormControl
	 * @param  mixed
	 * @return bool
	 */
	public static function validateEqual(IFormControl $control, $arg)
	{
		$value = $control->getValue();
		foreach ((is_array($value) ? $value : array($value)) as $val) {
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				if ((string) $val === (string) ($item instanceof IFormControl ? $item->value : $item)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}



	/**
	 * Filled validator: is control filled?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateFilled(IFormControl $control)
	{
		return (string) $control->getValue() !== ''; // NULL, FALSE, '' ==> FALSE
	}



	/**
	 * Valid validator: is control valid?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateValid(IFormControl $control)
	{
		return $control->rules->validate(TRUE);
	}



	/**
	 * Adds error message to the list.
	 * @param  string  error message
	 * @return void
	 */
	public function addError($message)
	{
		if (!in_array($message, $this->errors, TRUE)) {
			$this->errors[] = $message;
		}
		$this->getForm()->addError($message);
	}



	/**
	 * Returns errors corresponding to control.
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}



	/**
	 * @return bool
	 */
	public function hasErrors()
	{
		return (bool) $this->errors;
	}



	/**
	 * @return void
	 */
	public function cleanErrors()
	{
		$this->errors = array();
	}

}
