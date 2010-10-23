<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Security
 */



/**
 * Trivial implementation of IAuthenticator.
 *
 * @author     David Grudl
 */
class NSimpleAuthenticator extends NObject implements IAuthenticator
{
	/** @var array */
	private $userlist;


	/**
	 * @param  array  list of usernames and passwords
	 */
	public function __construct(array $userlist)
	{
		$this->userlist = $userlist;
	}



	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws NAuthenticationException
	 * @param  array
	 * @return IIdentity
	 * @throws NAuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		$username = $credentials[self::USERNAME];
		foreach ($this->userlist as $name => $pass) {
			if (strcasecmp($name, $credentials[self::USERNAME]) === 0) {
				if (strcasecmp($pass, $credentials[self::PASSWORD]) === 0) {
					// matched!
					return new NIdentity($name);
				}

				throw new NAuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
			}
		}

		throw new NAuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
	}

}
