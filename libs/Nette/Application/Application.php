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
 * Front Controller.
 *
 * @author     David Grudl
 */
class NApplication extends NObject
{
	/** @var int */
	public static $maxLoop = 20;

	/** @var array */
	public $defaultServices = array(
		'Nette\\Application\\IRouter' => 'NMultiRouter',
		'Nette\\Application\\IPresenterLoader' => array(__CLASS__, 'createPresenterLoader'),
	);

	/** @var bool enable fault barrier? */
	public $catchExceptions;

	/** @var string */
	public $errorPresenter;

	/** @var array of function(NApplication $sender); Occurs before the application loads presenter */
	public $onStartup;

	/** @var array of function(NApplication $sender, Exception $e = NULL); Occurs before the application shuts down */
	public $onShutdown;

	/** @var array of function(NApplication $sender, NPresenterRequest $request); Occurs when a new request is ready for dispatch */
	public $onRequest;

	/** @var array of function(NApplication $sender, Exception $e); Occurs when an unhandled exception occurs in the application */
	public $onError;

	/** @var array of string */
	public $allowedMethods = array('GET', 'POST', 'HEAD', 'PUT', 'DELETE');

	/** @var array of NPresenterRequest */
	private $requests = array();

	/** @var NPresenter */
	private $presenter;

	/** @var NServiceLocator */
	private $serviceLocator;



	/**
	 * Dispatch a HTTP request to a front controller.
	 * @return void
	 */
	public function run()
	{
		$httpRequest = $this->getHttpRequest();
		$httpResponse = $this->getHttpResponse();

		$httpRequest->setEncoding('UTF-8');
		$httpResponse->setHeader('X-Powered-By', 'Nette Framework');

		if (NEnvironment::getVariable('baseUri') === NULL) {
			NEnvironment::setVariable('baseUri', $httpRequest->getUri()->getBasePath());
		}

		// autostarts session
		$session = $this->getSession();
		if (!$session->isStarted() && $session->exists()) {
			$session->start();
		}

		// check HTTP method
		if ($this->allowedMethods) {
			$method = $httpRequest->getMethod();
			if (!in_array($method, $this->allowedMethods, TRUE)) {
				$httpResponse->setCode(IHttpResponse::S501_NOT_IMPLEMENTED);
				$httpResponse->setHeader('Allow', implode(',', $this->allowedMethods));
				echo '<h1>Method ' . htmlSpecialChars($method) . ' is not implemented</h1>';
				return;
			}
		}

		// dispatching
		$request = NULL;
		$repeatedError = FALSE;
		do {
			try {
				if (count($this->requests) > self::$maxLoop) {
					throw new NApplicationException('Too many loops detected in application life cycle.');
				}

				if (!$request) {
					$this->onStartup($this);

					// default router
					$router = $this->getRouter();
					if ($router instanceof NMultiRouter && !count($router)) {
						$router[] = new NSimpleRouter(array(
							'presenter' => 'Default',
							'action' => 'default',
						));
					}

					// routing
					$request = $router->match($httpRequest);
					if (!($request instanceof NPresenterRequest)) {
						$request = NULL;
						throw new NBadRequestException('No route for HTTP request.');
					}

					if (strcasecmp($request->getPresenterName(), $this->errorPresenter) === 0) {
						throw new NBadRequestException('Invalid request.');
					}
				}

				$this->requests[] = $request;
				$this->onRequest($this, $request);

				// Instantiate presenter
				$presenter = $request->getPresenterName();
				try {
					$class = $this->getPresenterLoader()->getPresenterClass($presenter);
					$request->setPresenterName($presenter);
				} catch (NInvalidPresenterException $e) {
					throw new NBadRequestException($e->getMessage(), 404, $e);
				}
				$request->freeze();

				// Execute presenter
				$this->presenter = new $class;
				$response = $this->presenter->run($request);

				// Send response
				if ($response instanceof NForwardingResponse) {
					$request = $response->getRequest();
					continue;

				} elseif ($response instanceof IPresenterResponse) {
					$response->send();
				}
				break;

			} catch (Exception $e) {
				// fault barrier
				if ($this->catchExceptions === NULL) {
					$this->catchExceptions = NEnvironment::isProduction();
				}

				$this->onError($this, $e);

				if (!$this->catchExceptions) {
					$this->onShutdown($this, $e);
					throw $e;
				}

				if ($repeatedError) {
					$e = new NApplicationException('An error occured while executing error-presenter', 0, $e);
				}

				if (!$httpResponse->isSent()) {
					$httpResponse->setCode($e instanceof NBadRequestException ? $e->getCode() : 500);
				}

				if (!$repeatedError && $this->errorPresenter) {
					$repeatedError = TRUE;
					$request = new NPresenterRequest(
						$this->errorPresenter,
						NPresenterRequest::FORWARD,
						array('exception' => $e)
					);
					// continue

				} else { // default error handler
					echo "<meta name='robots' content='noindex'>\n\n";
					if ($e instanceof NBadRequestException) {
						echo "<title>404 Not Found</title>\n\n<h1>Not Found</h1>\n\n<p>The requested URL was not found on this server.</p>";

					} else {
						NDebug::processException($e, FALSE);
						echo "<title>500 Internal Server Error</title>\n\n<h1>Server Error</h1>\n\n",
							"<p>The server encountered an internal error and was unable to complete your request. Please try again later.</p>";
					}
					echo "\n\n<hr>\n<small><i>Nette Framework</i></small>";
					break;
				}
			}
		} while (1);

		$this->onShutdown($this, isset($e) ? $e : NULL);
	}



	/**
	 * Returns all processed requests.
	 * @return array of NPresenterRequest
	 */
	final public function getRequests()
	{
		return $this->requests;
	}



	/**
	 * Returns current presenter.
	 * @return NPresenter
	 */
	final public function getPresenter()
	{
		return $this->presenter;
	}



	/********************* services ****************d*g**/



	/**
	 * Gets the service locator (experimental).
	 * @return IServiceLocator
	 */
	final public function getServiceLocator()
	{
		if ($this->serviceLocator === NULL) {
			$this->serviceLocator = new NServiceLocator(NEnvironment::getServiceLocator());

			foreach ($this->defaultServices as $name => $service) {
				if (!$this->serviceLocator->hasService($name)) {
					$this->serviceLocator->addService($name, $service);
				}
			}
		}
		return $this->serviceLocator;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @param  array  options in case service is not singleton
	 * @return object
	 */
	final public function getService($name, array $options = NULL)
	{
		return $this->getServiceLocator()->getService($name, $options);
	}



	/**
	 * Returns router.
	 * @return IRouter
	 */
	public function getRouter()
	{
		return $this->getServiceLocator()->getService('Nette\\Application\\IRouter');
	}



	/**
	 * Changes router.
	 * @param  IRouter
	 * @return NApplication  provides a fluent interface
	 */
	public function setRouter(IRouter $router)
	{
		$this->getServiceLocator()->addService('Nette\\Application\\IRouter', $router);
		return $this;
	}



	/**
	 * Returns presenter loader.
	 * @return IPresenterLoader
	 */
	public function getPresenterLoader()
	{
		return $this->getServiceLocator()->getService('Nette\\Application\\IPresenterLoader');
	}



	/********************* service factories ****************d*g**/



	/**
	 * @return IPresenterLoader
	 */
	public static function createPresenterLoader()
	{
		return new NPresenterLoader(NEnvironment::getVariable('appDir'));
	}



	/********************* request serialization ****************d*g**/



	/**
	 * Stores current request to session.
	 * @param  mixed  optional expiration time
	 * @return string key
	 */
	public function storeRequest($expiration = '+ 10 minutes')
	{
		$session = $this->getSession('Nette.Application/requests');
		do {
			$key = substr(md5(lcg_value()), 0, 4);
		} while (isset($session[$key]));

		$session[$key] = end($this->requests);
		$session->setExpiration($expiration, $key);
		return $key;
	}



	/**
	 * Restores current request to session.
	 * @param  string key
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$session = $this->getSession('Nette.Application/requests');
		if (isset($session[$key])) {
			$request = clone $session[$key];
			unset($session[$key]);
			$request->setFlag(NPresenterRequest::RESTORED, TRUE);
			$this->presenter->terminate(new NForwardingResponse($request));
		}
	}



	/********************* backend ****************d*g**/



	/**
	 * @return IHttpRequest
	 */
	protected function getHttpRequest()
	{
		return NEnvironment::getHttpRequest();
	}



	/**
	 * @return IHttpResponse
	 */
	protected function getHttpResponse()
	{
		return NEnvironment::getHttpResponse();
	}



	/**
	 * @return NSession
	 */
	protected function getSession($namespace = NULL)
	{
		return NEnvironment::getSession($namespace);
	}

}
