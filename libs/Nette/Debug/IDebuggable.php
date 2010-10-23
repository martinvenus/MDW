<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette
 */



/**
 * Custom output for NDebug.
 *
 * @author     David Grudl
 */
interface IDebuggable
{

	/**
	 * Returns custom panels.
	 * @return array
	 */
	function getPanels();

}
