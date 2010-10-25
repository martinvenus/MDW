<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class Admin_BasePresenter extends BasePresenter {

    protected $user;
    public $oldLayoutMode = FALSE;

    public function startup() {

        parent::startup();

        if (!function_exists('lcfirst')) {

            function lcfirst($str) {
                $str{0} = strtolower($str{0});
                return $str;
            }

        }

        if (Environment::getServiceLocator()->hasService('User') === false) {
            Environment::getServiceLocator()->addService('User', new User());
        }

        $this->user = Environment::getServiceLocator()->getService('User');

        $this->verifyUser();

        // Nastaví aktuální identitu do template proměnné user
        $this->template->user = $this->user->isLoggedIn() ? $this->user->getIdentity() : NULL;

        if (!defined('ERROR_MESSAGE')) {
            define('ERROR_MESSAGE', 'System exception occured.');
        }

        if (!defined('HASH_TYPE')) {
            define('HASH_TYPE', 'sha512');
        }
    }

    /*
     * Metoda, která ověří, zda je uživatel přihlášen
     * V případě, že není -> přesměruje na přihlášení
     */

    protected function verifyUser() {

        $backlink = '';

        if (!$this->user->isLoggedIn()) {
            if ($this->user->getLogoutReason() === User::INACTIVITY) {
                $backlink = $this->getApplication()->storeRequest();
                $this->flashMessage('You was logged out due to inactivity. Please log again.');
            }

            if (strcmp($this->getName(), 'Admin:Login') != 0) {
                $this->redirect('Login:', $backlink);
            }
        }
    }

}
