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
 * Forwards to new request.
 *
 * @author     David Grudl
 */
class NForwardingResponse extends NObject implements IPresenterResponse
{
	/** @var NPresenterRequest */
	private $request;



	/**
	 * @param  NPresenterRequest  new request
	 */
	public function __construct(NPresenterRequest $request)
	{
		$this->request = $request;
	}



	/**
	 * @return NPresenterRequest
	 */
	final public function getRequest()
	{
		return $this->request;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send()
	{
	}

}
