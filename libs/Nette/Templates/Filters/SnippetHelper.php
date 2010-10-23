<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Templates
 */



/**
 * NControl snippet template helper.
 *
 * @author     David Grudl
 */
class NSnippetHelper extends NObject
{
	/** @var bool */
	public static $outputAllowed = TRUE;

	/** @var string */
	private $id;

	/** @var string */
	private $tag;

	/** @var ArrayObject */
	private $payload;

	/** @var int */
	private $level;



	/**
	 * Starts conditional snippet rendering. Returns NSnippetHelper object if snippet was started.
	 * @param  NControl control
	 * @param  string  snippet name
	 * @param  string  start element
	 * @return NSnippetHelper
	 */
	public static function create(NControl $control, $name = NULL, $tag = 'div')
	{
		if (self::$outputAllowed) { // rendering flow or non-AJAX request
			$obj = new self;
			$obj->tag = trim($tag, '<>');
			if ($obj->tag) echo '<', $obj->tag, ' id="', $control->getSnippetId($name), '">';
			return $obj; // or string?

		} elseif ($control->isControlInvalid($name)) { // start snippet buffering
			$obj = new self;
			$obj->id = $control->getSnippetId($name);
			$obj->payload = $control->getPresenter()->getPayload();
			ob_start();
			$obj->level = ob_get_level();
			self::$outputAllowed = TRUE;
			return $obj;

		} else {
			return FALSE;
		}
	}



	/**
	 * Finishes and saves the snippet.
	 * @return void
	 */
	public function finish()
	{
		if ($this->tag !== NULL) { // rendering flow or non-AJAX request
			if ($this->tag) echo "</$this->tag>";

		} else {  // finish snippet buffering
			if ($this->level !== ob_get_level()) {
				throw new InvalidStateException("Snippet '$this->id' cannot be ended here.");
			}
			$this->payload->snippets[$this->id] = ob_get_clean();
			self::$outputAllowed = FALSE;
		}
	}

}
