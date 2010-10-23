<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

// no namespace



/**
 * NCallback factory.
 * @param  mixed   class, object, function, callback
 * @param  string  method
 * @return NCallback
 */
function callback($callback, $m = NULL)
{
	return ($m === NULL && $callback instanceof NCallback) ? $callback : new NCallback($callback, $m);
}



/**
 * NDebug::dump shortcut.
 */
function dump($var)
{
	foreach (func_get_args() as $arg) NDebug::dump($arg);
	return $var;
}
