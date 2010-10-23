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
 * NAnnotations support for PHP.
 *
 * @author     David Grudl
 * @deprecated
 */
final class NAnnotations
{
	/** @var bool */
	public static $useReflection;



	/**
	 * Has class/method/property specified annotation?
	 * @param  ReflectionClass|ReflectionMethod|ReflectionProperty
	 * @param  string    annotation name
	 * @return bool
	 */
	public static function has(Reflector $r, $name)
	{
		trigger_error(__METHOD__ . '() is deprecated; use getReflection()->hasAnnotation() instead.', E_USER_WARNING);
		$cache = NAnnotationsParser::getAll($r);
		return !empty($cache[$name]);
	}



	/**
	 * Returns an annotation value.
	 * @param  ReflectionClass|ReflectionMethod|ReflectionProperty
	 * @param  string    annotation name
	 * @return array
	 */
	public static function get(Reflector $r, $name)
	{
		trigger_error(__METHOD__ . '() is deprecated; use getReflection()->getAnnotation() instead.', E_USER_WARNING);
		$cache = NAnnotationsParser::getAll($r);
		return isset($cache[$name]) ? end($cache[$name]) : NULL;
	}



	/**
	 * Returns all annotations.
	 * @param  ReflectionClass|ReflectionMethod|ReflectionProperty
	 * @param  string    annotation name
	 * @return array
	 */
	public static function getAll(Reflector $r, $name = NULL)
	{
		trigger_error(__METHOD__ . '() is deprecated; use getReflection()->getAnnotations() instead.', E_USER_WARNING);
		$cache = NAnnotationsParser::getAll($r);

		if ($name === NULL) {
			return $cache;

		} elseif (isset($cache[$name])) {
			return $cache[$name];

		} else {
			return array();
		}
	}

}
