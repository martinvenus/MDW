<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Web
 */



/**
 * NUser authentication and authorization.
 *
 * @author     David Grudl
 *
 * @property-read IIdentity $identity
 * @property   IAuthenticator $authenticationHandler
 * @property   IAuthorizator $authorizationHandler
 * @property-read int $logoutReason
 * @property-read array $roles
 * @property-read bool $authenticated
 */
class NUser extends NObject implements IUser
{
	/**#@+ log-out reason {@link NUser::getLogoutReason()} */
	const MANUAL = 1;
	const INACTIVITY = 2;
	const BROWSER_CLOSED = 3;
	/**#@-*/

	/** @var string  default role for unauthenticated user */
	public $guestRole = 'guest';

	/** @var string  default role for authenticated user without own identity */
	public $authenticatedRole = 'authenticated';

	/** @var array of function(NUser $sender); Occurs when the user is successfully logged in */
	public $onLoggedIn;

	/** @var array of function(NUser $sender); Occurs when the user is logged out */
	public $onLoggedOut;

	/** @deprecated */
	public $onAuthenticated;

	/** @deprecated */
	public $onSignedOut;

	/** @var IAuthenticator */
	private $authenticationHandler;

	/** @var IAuthorizator */
	private $authorizationHandler;

	/** @var string */
	private $namespace = '';

	/** @var NSessionNamespace */
	private $session;



	public function __construct()
	{
		// back compatiblity
		$this->onLoggedIn = & $this->onAuthenticated;
		$this->onLoggedOut = & $this->onSignedOut;
	}



	/********************* Authentication ****************d*g**/



	/**
	 * Conducts the authentication process.
	 * @param  string
	 * @param  string
	 * @param  mixed
	 * @return void
	 * @throws NAuthenticationException if authentication was not successful
	 */
	public function login($username, $password, $extra = NULL)
	{
		$handler = $this->getAuthenticationHandler();
		if ($handler === NULL) {
			throw new InvalidStateException('Authentication handler has not been set.');
		}

		$this->logout(TRUE);

		$credentials = array(
			IAuthenticator::USERNAME => $username,
			IAuthenticator::PASSWORD => $password,
			'extra' => $extra,
		);

		$this->setIdentity($handler->authenticate($credentials));
		$this->setAuthenticated(TRUE);
		$this->onLoggedIn($this);
	}



	/**
	 * Logs out the user from the current session.
	 * @param  bool  clear the identity from persistent storage?
	 * @return void
	 */
	final public function logout($clearIdentity = FALSE)
	{
		if ($this->isLoggedIn()) {
			$this->setAuthenticated(FALSE);
			$this->onLoggedOut($this);
		}

		if ($clearIdentity) {
			$this->setIdentity(NULL);
		}
	}



	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	final public function isLoggedIn()
	{
		$session = $this->getSessionNamespace(FALSE);
		return $session && $session->authenticated;
	}



	/**
	 * Returns current user identity, if any.
	 * @return IIdentity
	 */
	final public function getIdentity()
	{
		$session = $this->getSessionNamespace(FALSE);
		return $session ? $session->identity : NULL;
	}



	/**
	 * Sets authentication handler.
	 * @param  IAuthenticator
	 * @return NUser  provides a fluent interface
	 */
	public function setAuthenticationHandler(IAuthenticator $handler)
	{
		$this->authenticationHandler = $handler;
		return $this;
	}



	/**
	 * Returns authentication handler.
	 * @return IAuthenticator
	 */
	final public function getAuthenticationHandler()
	{
		if ($this->authenticationHandler === NULL) {
			$this->authenticationHandler = NEnvironment::getService('Nette\\Security\\IAuthenticator');
		}
		return $this->authenticationHandler;
	}



	/**
	 * Changes namespace; allows more users to share a session.
	 * @param  string
	 * @return NUser  provides a fluent interface
	 */
	public function setNamespace($namespace)
	{
		if ($this->namespace !== $namespace) {
			$this->namespace = (string) $namespace;
			$this->session = NULL;
		}
		return $this;
	}



	/**
	 * Returns current namespace.
	 * @return string
	 */
	final public function getNamespace()
	{
		return $this->namespace;
	}



	/**
	 * Enables log out after inactivity.
	 * @param  string|int|DateTime number of seconds or timestamp
	 * @param  bool  log out when the browser is closed?
	 * @param  bool  clear the identity from persistent storage?
	 * @return NUser  provides a fluent interface
	 */
	public function setExpiration($time, $whenBrowserIsClosed = TRUE, $clearIdentity = FALSE)
	{
		$session = $this->getSessionNamespace(TRUE);
		if ($time) {
			$time = NTools::createDateTime($time)->format('U');
			$session->expireTime = $time;
			$session->expireDelta = $time - time();

		} else {
			unset($session->expireTime, $session->expireDelta);
		}

		$session->expireIdentity = (bool) $clearIdentity;
		$session->expireBrowser = (bool) $whenBrowserIsClosed;
		$session->browserCheck = TRUE;
		$session->setExpiration(0, 'browserCheck');
		return $this;
	}



	/**
	 * Why was user logged out?
	 * @return int
	 */
	final public function getLogoutReason()
	{
		$session = $this->getSessionNamespace(FALSE);
		return $session ? $session->reason : NULL;
	}



	/**
	 * Returns and initializes $this->session.
	 * @return NSessionNamespace
	 */
	protected function getSessionNamespace($need)
	{
		if ($this->session !== NULL) {
			return $this->session;
		}

		$sessionHandler = $this->getSession();
		if (!$need && !$sessionHandler->exists()) {
			return NULL;
		}

		$this->session = $session = $sessionHandler->getNamespace('Nette.Web.User/' . $this->namespace);

		if (!($session->identity instanceof IIdentity) || !is_bool($session->authenticated)) {
			$session->remove();
		}

		if ($session->authenticated && $session->expireBrowser && !$session->browserCheck) { // check if browser was closed?
			$session->reason = self::BROWSER_CLOSED;
			$session->authenticated = FALSE;
			$this->onLoggedOut($this);
			if ($session->expireIdentity) {
				unset($session->identity);
			}
		}

		if ($session->authenticated && $session->expireDelta > 0) { // check time expiration
			if ($session->expireTime < time()) {
				$session->reason = self::INACTIVITY;
				$session->authenticated = FALSE;
				$this->onLoggedOut($this);
				if ($session->expireIdentity) {
					unset($session->identity);
				}
			}
			$session->expireTime = time() + $session->expireDelta; // sliding expiration
		}

		if (!$session->authenticated) {
			unset($session->expireTime, $session->expireDelta, $session->expireIdentity,
				$session->expireBrowser, $session->browserCheck, $session->authTime);
		}

		return $this->session;
	}



	/**
	 * Sets the authenticated status of this user.
	 * @param  bool  flag indicating the authenticated status of user
	 * @return NUser  provides a fluent interface
	 */
	protected function setAuthenticated($state)
	{
		$session = $this->getSessionNamespace(TRUE);
		$session->authenticated = (bool) $state;

		// NSession Fixation defence
		$this->getSession()->regenerateId();

		if ($state) {
			$session->reason = NULL;
			$session->authTime = time(); // informative value

		} else {
			$session->reason = self::MANUAL;
			$session->authTime = NULL;
		}
		return $this;
	}



	/**
	 * Sets the user identity.
	 * @param  IIdentity
	 * @return NUser  provides a fluent interface
	 */
	protected function setIdentity(IIdentity $identity = NULL)
	{
		$this->getSessionNamespace(TRUE)->identity = $identity;
		return $this;
	}



	/********************* Authorization ****************d*g**/



	/**
	 * Returns a list of effective roles that a user has been granted.
	 * @return array
	 */
	public function getRoles()
	{
		if (!$this->isLoggedIn()) {
			return array($this->guestRole);
		}

		$identity = $this->getIdentity();
		return $identity ? $identity->getRoles() : array($this->authenticatedRole);
	}



	/**
	 * Is a user in the specified effective role?
	 * @param  string
	 * @return bool
	 */
	final public function isInRole($role)
	{
		return in_array($role, $this->getRoles(), TRUE);
	}



	/**
	 * Has a user effective access to the Resource?
	 * If $resource is NULL, then the query applies to all resources.
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	public function isAllowed($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL)
	{
		$handler = $this->getAuthorizationHandler();
		if (!$handler) {
			throw new InvalidStateException("Authorization handler has not been set.");
		}

		foreach ($this->getRoles() as $role) {
			if ($handler->isAllowed($role, $resource, $privilege)) return TRUE;
		}

		return FALSE;
	}



	/**
	 * Sets authorization handler.
	 * @param  IAuthorizator
	 * @return NUser  provides a fluent interface
	 */
	public function setAuthorizationHandler(IAuthorizator $handler)
	{
		$this->authorizationHandler = $handler;
		return $this;
	}



	/**
	 * Returns current authorization handler.
	 * @return IAuthorizator
	 */
	final public function getAuthorizationHandler()
	{
		if ($this->authorizationHandler === NULL) {
			$this->authorizationHandler = NEnvironment::getService('Nette\\Security\\IAuthorizator');
		}
		return $this->authorizationHandler;
	}



	/********************* backend ****************d*g**/



	/**
	 * Returns session handler.
	 * @return NSession
	 */
	protected function getSession()
	{
		return NEnvironment::getSession();
	}



	/**#@+ @deprecated */
	function authenticate($username, $password, $extra = NULL)
	{
		trigger_error(__METHOD__ . '() is deprecated; use login() instead.', E_USER_WARNING);
		return $this->login($username, $password, $extra);
	}

	function signOut($clearIdentity = FALSE)
	{
		trigger_error(__METHOD__ . '() is deprecated; use logout() instead.', E_USER_WARNING);
		return $this->logout($clearIdentity);
	}

	function isAuthenticated()
	{
		trigger_error(__METHOD__ . '() is deprecated; use isLoggedIn() instead.', E_USER_WARNING);
		return $this->isLoggedIn();
	}

	function getSignOutReason()
	{
		trigger_error(__METHOD__ . '() is deprecated; use getLogoutReason() instead.', E_USER_WARNING);
		return $this->getLogoutReason();
	}
	/**#@-*/

}
