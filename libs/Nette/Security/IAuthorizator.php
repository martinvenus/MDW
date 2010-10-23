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
 * Authorizator checks if a given role has authorization
 * to access a given resource.
 *
 * @author     David Grudl
 */
interface IAuthorizator
{
	/** NSet type: all */
	const ALL = NULL;

	/** NPermission type: allow */
	const ALLOW = TRUE;

	/** NPermission type: deny */
	const DENY = FALSE;


	/**
	 * Performs a role-based authorization.
	 * @param  string  role
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL);

}
