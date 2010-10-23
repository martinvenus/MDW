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
 * Caching template helper.
 *
 * @author     David Grudl
 */
class NCachingHelper extends NObject
{
	/** @var array */
	private $frame;

	/** @var string */
	private $key;



	/**
	 * Starts the output cache. Returns NCachingHelper object if buffering was started.
	 * @param  string
	 * @param  string
	 * @param  array
	 * @return NCachingHelper
	 */
	public static function create($key, $file, $tags)
	{
		$cache = self::getCache();
		if (isset($cache[$key])) {
			echo $cache[$key];
			return FALSE;

		} else {
			$obj = new self;
			$obj->key = $key;
			$obj->frame = array(
				NCache::FILES => array($file),
				NCache::TAGS => $tags,
				NCache::EXPIRE => rand(86400 * 4, 86400 * 7),
			);
			ob_start();
			return $obj;
		}
	}



	/**
	 * Stops and saves the cache.
	 * @return void
	 */
	public function save()
	{
		$this->getCache()->save($this->key, ob_get_flush(), $this->frame);
		$this->key = $this->frame = NULL;
	}



	/**
	 * Adds the file dependency.
	 * @param  string
	 * @return void
	 */
	public function addFile($file)
	{
		$this->frame[NCache::FILES][] = $file;
	}



	/**
	 * Adds the cached item dependency.
	 * @param  string
	 * @return void
	 */
	public function addItem($item)
	{
		$this->frame[NCache::ITEMS][] = $item;
	}



	/********************* backend ****************d*g**/



	/**
	 * @return NCache
	 */
	protected static function getCache()
	{
		return NEnvironment::getCache('Nette.Template.Curly');
	}

}