<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Reflection
 */



/**
 * Reports information about a classes variable.
 *
 * @author     David Grudl
 */
class NPropertyReflection extends ReflectionProperty
{

	public function __toString()
	{
		return 'Property ' . parent::getDeclaringClass()->getName() . '::$' . $this->getName();
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return NClassReflection
	 */
	public function getDeclaringClass()
	{
		return new NClassReflection(parent::getDeclaringClass()->getName());
	}



	/********************* NAnnotations support ****************d*g**/



	/**
	 * Has property specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		$res = NAnnotationsParser::getAll($this);
		return !empty($res[$name]);
	}



	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return IAnnotation
	 */
	public function getAnnotation($name)
	{
		$res = NAnnotationsParser::getAll($this);
		return isset($res[$name]) ? end($res[$name]) : NULL;
	}



	/**
	 * Returns all annotations.
	 * @return array
	 */
	public function getAnnotations()
	{
		return NAnnotationsParser::getAll($this);
	}



	/********************* NObject behaviour ****************d*g**/



	/**
	 * @return NClassReflection
	 */
	public function getReflection()
	{
		return new NClassReflection($this);
	}



	public function __call($name, $args)
	{
		return NObjectMixin::call($this, $name, $args);
	}



	public function &__get($name)
	{
		return NObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return NObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return NObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		throw new MemberAccessException("Cannot unset the property {$this->reflection->name}::\$$name.");
	}

}
