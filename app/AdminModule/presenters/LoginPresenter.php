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

        /*
     * Vykreslení stránky pro administraci automobilů
    */
    public function renderDefault($backlink) {

        $this['loginForm']; // získá komponentu

    }

    protected function createComponentLoginForm(){

        //$this->backlink = $backlink;

        $form = new AppForm;

        $form->addText('userName', 'Uživatelské jméno:')
                ->addRule(Form::FILLED, 'Uživatelské jméno musí být vyplněno.');

        $form->addPassword('password', 'Přístupové heslo:')
                ->addRule(Form::FILLED, 'Přístupové heslo musí být vyplněno.');
        //->addRule(Form::MIN_LENGTH, 'Heslo musí být minimálně %d znaků', 12);


        $form->addSubmit('login', 'Přihlásit');


        $form->onSubmit[] = callback($this, 'loginFormSubmitted');

        $this->user->setAuthenticationHandler(new UsersModel());

        return $form;

    }



    /*
     * Metoda zpracovávající data z formuláře
     * @param $form data z formuláře
     */

    
    public function loginFormSubmitted($formular){

        $formular = $formular->getValues();

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

            $this->flashMessage('Bylo zadáno špatné uživatelské jméno nebo heslo.');
        }

        if ($this->user->isLoggedIn()) {

            $this->flashMessage('Přihlášení proběhlo úspěšně.');

            if (!empty($this->backlink)) {
                $this->getApplication()->restoreRequest($this->backlink);
            }

            $this->redirect('Default:');
        }
    }

}
