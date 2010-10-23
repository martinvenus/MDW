<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Application
 */



/**
 * Default presenter loader.
 *
 * @author     David Grudl
 */
class NPresenterLoader implements IPresenterLoader
{
	/** @var bool */
	public $caseSensitive = FALSE;

	/** @var string */
	private $baseDir;

	/** @var array */
	private $cache = array();



	/**
	 * @param  string
	 */
	public function __construct($baseDir)
	{
		$this->baseDir = $baseDir;
	}



	/**
	 * @param  string  presenter name
	 * @return string  class name
	 * @throws NInvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (isset($this->cache[$name])) {
			list($class, $name) = $this->cache[$name];
			return $class;
		}

		if (!is_string($name) || !preg_match("#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#", $name)) {
			throw new NInvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$class = $this->formatPresenterClass($name);

		if (!class_exists($class)) {
			// internal autoloading
			$file = $this->formatPresenterFile($name);
			if (is_file($file) && is_readable($file)) {
				NLimitedScope::load($file);
			}

			if (!class_exists($class)) {
				throw new NInvalidPresenterException("Cannot load presenter '$name', class '$class' was not found in '$file'.");
			}
		}

		$reflection = new NClassReflection($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('IPresenter')) {
			throw new NInvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		}

		if ($reflection->isAbstract()) {
			throw new NInvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		// canonicalize presenter name
		$realName = $this->unformatPresenterClass($class);
		if ($name !== $realName) {
			if ($this->caseSensitive) {
				throw new NInvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
			} else {
				$this->cache[$name] = array($class, $realName);
				$name = $realName;
			}
		} else {
			$this->cache[$name] = array($class, $realName);
		}

		return $class;
	}



	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 */
	public function formatPresenterClass($presenter)
	{
		return strtr($presenter, ':', '_') . 'Presenter';
		return str_replace(':', 'Module\\', $presenter) . 'Presenter';
	}



	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		return strtr(substr($class, 0, -9), '_', ':');
		return str_replace('Module\\', ':', substr($class, 0, -9));
	}



	/**
	 * Formats presenter class file name.
	 * @param  string
	 * @return string
	 */
	public function formatPresenterFile($presenter)
	{
		$path = '/' . str_replace(':', 'Module/', $presenter);
		return $this->baseDir . substr_replace($path, '/presenters', strrpos($path, '/'), 0) . 'Presenter.php';
	}

}
