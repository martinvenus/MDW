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
 * NEnvironment helper.
 *
 * @author     David Grudl
 */
class NConfigurator extends NObject
{
	/** @var string */
	public $defaultConfigFile = '%appDir%/config.ini';

	/** @var array */
	public $defaultServices = array(
		'Nette\\Application\\Application' => 'NApplication',
		'Nette\\Web\\HttpContext' => 'NHttpContext',
		'Nette\\Web\\IHttpRequest' => 'NHttpRequest',
		'Nette\\Web\\IHttpResponse' => 'NHttpResponse',
		'Nette\\Web\\IUser' => 'NUser',
		'Nette\\Caching\\ICacheStorage' => array(__CLASS__, 'createCacheStorage'),
		'Nette\\Web\\Session' => 'NSession',
		'Nette\\Loaders\\RobotLoader' => array(__CLASS__, 'createRobotLoader'),
	);



	/**
	 * Detect environment mode.
	 * @param  string mode name
	 * @return bool
	 */
	public function detect($name)
	{
		switch ($name) {
		case 'environment':
			// environment name autodetection
			if ($this->detect('console')) {
				return NEnvironment::CONSOLE;

			} else {
				return NEnvironment::getMode('production') ? NEnvironment::PRODUCTION : NEnvironment::DEVELOPMENT;
			}

		case 'production':
			// detects production mode by server IP address
			if (PHP_SAPI === 'cli') {
				return FALSE;

			} elseif (isset($_SERVER['SERVER_ADDR']) || isset($_SERVER['LOCAL_ADDR'])) {
				$addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
				$oct = explode('.', $addr);
				// 10.0.0.0/8   Private network
				// 127.0.0.0/8  Loopback
				// 169.254.0.0/16 & ::1  Link-Local
				// 172.16.0.0/12  Private network
				// 192.168.0.0/16  Private network
				return $addr !== '::1' && (count($oct) !== 4 || ($oct[0] !== '10' && $oct[0] !== '127' && ($oct[0] !== '172' || $oct[1] < 16 || $oct[1] > 31)
					&& ($oct[0] !== '169' || $oct[1] !== '254') && ($oct[0] !== '192' || $oct[1] !== '168')));

			} else {
				return TRUE;
			}

		case 'console':
			return PHP_SAPI === 'cli';

		default:
			// unknown mode
			return NULL;
		}
	}



	/**
	 * Loads global configuration from file and process it.
	 * @param  string|NConfig  file name or NConfig object
	 * @return NConfig
	 */
	public function loadConfig($file)
	{
		$name = NEnvironment::getName();

		if ($file instanceof NConfig) {
			$config = $file;
			$file = NULL;

		} else {
			if ($file === NULL) {
				$file = $this->defaultConfigFile;
			}
			$file = NEnvironment::expand($file);
			$config = NConfig::fromFile($file, $name, 0);
		}

		// process environment variables
		if ($config->variable instanceof NConfig) {
			foreach ($config->variable as $key => $value) {
				NEnvironment::setVariable($key, $value);
			}
		}

		$config->expand();

		// process services
		$runServices = array();
		$locator = NEnvironment::getServiceLocator();
		if ($config->service instanceof NConfig) {
			foreach ($config->service as $key => $value) {
				$key = strtr($key, '-', '\\'); // limited INI chars
				if (is_string($value)) {
					$locator->removeService($key);
					$locator->addService($key, $value);
				} else {
					if ($value->factory) {
						$locator->removeService($key);
						$locator->addService($key, $value->factory, isset($value->singleton) ? $value->singleton : TRUE, (array) $value->option);
					}
					if ($value->run) {
						$runServices[] = $key;
					}
				}
			}
		}

		// process ini settings
		if (!$config->php) { // backcompatibility
			$config->php = $config->set;
			unset($config->set);
		}

		if ($config->php instanceof NConfig) {
			if (PATH_SEPARATOR !== ';' && isset($config->php->include_path)) {
				$config->php->include_path = str_replace(';', PATH_SEPARATOR, $config->php->include_path);
			}

			foreach ($config->php as $key => $value) { // flatten INI dots
				if ($value instanceof NConfig) {
					unset($config->php->$key);
					foreach ($value as $k => $v) {
						$config->php->{"$key.$k"} = $v;
					}
				}
			}

			foreach ($config->php as $key => $value) {
				$key = strtr($key, '-', '.'); // backcompatibility

				if (!is_scalar($value)) {
					throw new InvalidStateException("Configuration value for directive '$key' is not scalar.");
				}

				if ($key === 'date.timezone') { // PHP bug #47466
					date_default_timezone_set($value);
				}

				if (function_exists('ini_set')) {
					ini_set($key, $value);
				} else {
					switch ($key) {
					case 'include_path':
						set_include_path($value);
						break;
					case 'iconv.internal_encoding':
						iconv_set_encoding('internal_encoding', $value);
						break;
					case 'mbstring.internal_encoding':
						mb_internal_encoding($value);
						break;
					case 'date.timezone':
						date_default_timezone_set($value);
						break;
					case 'error_reporting':
						error_reporting($value);
						break;
					case 'ignore_user_abort':
						ignore_user_abort($value);
						break;
					case 'max_execution_time':
						set_time_limit($value);
						break;
					default:
						if (ini_get($key) != $value) { // intentionally ==
							throw new NotSupportedException('Required function ini_set() is disabled.');
						}
					}
				}
			}
		}

		// define constants
		if ($config->const instanceof NConfig) {
			foreach ($config->const as $key => $value) {
				define($key, $value);
			}
		}

		// set modes
		if (isset($config->mode)) {
			foreach($config->mode as $mode => $state) {
				NEnvironment::setMode($mode, $state);
			}
		}

		// auto-start services
		foreach ($runServices as $name) {
			$locator->getService($name);
		}

		$config->freeze();
		return $config;
	}



	/********************* service factories ****************d*g**/



	/**
	 * Get initial instance of service locator.
	 * @return IServiceLocator
	 */
	public function createServiceLocator()
	{
		$locator = new NServiceLocator;
		foreach ($this->defaultServices as $name => $service) {
			$locator->addService($name, $service);
		}
		return $locator;
	}



	/**
	 * @return ICacheStorage
	 */
	public static function createCacheStorage()
	{
		return new NFileStorage(NEnvironment::getVariable('tempDir'));
	}



	/**
	 * @return NRobotLoader
	 */
	public static function createRobotLoader($options)
	{
		$loader = new NRobotLoader;
		$loader->autoRebuild = !NEnvironment::isProduction();
		//$loader->setCache(NEnvironment::getCache('Nette.NRobotLoader'));
		$dirs = isset($options['directory']) ? $options['directory'] : array(NEnvironment::getVariable('appDir'), NEnvironment::getVariable('libsDir'));
		$loader->addDirectory($dirs);
		$loader->register();
		return $loader;
	}

}
