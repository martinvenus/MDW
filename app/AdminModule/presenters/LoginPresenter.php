<?php

/**
 * Přihlášení k systému
 *
 * @copyright  Copyright (c) 2010 Martin Venuš, Jaroslav Líbal, Andrey Chervinka
 * @package    RTS
 */

/**
 * Login Presenter
 *
 * @author     Martin Venuš
 * @package    RTS
 */
class Admin_LoginPresenter extends Admin_BasePresenter {

    /** @var Form */
    protected $form;
    /** persistent */
    protected $backlink;

    /*
     * Konstruktor
     */
    public function startup() {

        parent::startup();

        $this->backlink = '';
    }

    public function actionDefault($backlink) {

        $this->backlink = $backlink;

        $this->form = new AppForm($this, 'login');

        $this->form->addText('userName', 'Uživatelské jméno:')
                ->addRule(Form::FILLED, 'Uživatelské jméno musí být vyplněno.');

        $this->form->addPassword('password', 'Přístupové heslo:')
                ->addRule(Form::FILLED, 'Přístupové heslo musí být vyplněno.');
        //->addRule(Form::MIN_LENGTH, 'Heslo musí být minimálně %d znaků', 12);


        $this->form->addSubmit('login', 'Přihlásit');


        $this->form->onSubmit[] = array($this, 'FormSubmitted');

        $this->template->form = $this->form;

        if (!isset($this->template->result)) {

            $this->template->result = "";

        }

        $this->user->setAuthenticationHandler(new UsersModel());
    }

    /*
     * Metoda zpracovávající data z formuláře
     * @param $form data z formuláře
     */

    function FormSubmitted(Form $form) {

        $formular = $this->form->getValues();

        try {
            // Ověření přihlašovacích údajů uživatele
            $this->user->login($formular['userName'], hash(HASH_TYPE, $formular['password'])); // předáme přihlašovací jméno a heslo

            /*
             * Doba odhlášení nastavena na 120 minut
             * Uživatel bude odhlášen při zavření prohlížeče
             * Při odhlášení uživatele bude smazána identita
             */
            $this->user->setExpiration(120 * 60, TRUE, TRUE);
        } catch (Exception $e) {

            $this->flashMessage('Wrong user name or password given.');
        }

        if ($this->user->isLoggedIn()) {

            $this->flashMessage('You are sucesfully logged in.');

            if (!empty($this->backlink)) {
                $this->getApplication()->restoreRequest($this->backlink);
            }

            $this->redirect('Default:');
        }
    }

}
