<?php
/**
 * Request Tracking System
 * MI-MDW at CZECH TECHNICAL UNIVERSITY IN PRAGUE
 *
 * @copyright  Copyright (c) 2010
 * @package    RTS
 * @author     Andrey Chervinka, Jaroslav Líbal, Martin Venuš
 */

/**
 *
 * Login to the system
 *
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
     * Přihlašovací formulář
     * @param backlink odkaz na stránku, na které se uživatel nacházel před odhlášením
     */
    public function actionDefault($backlink) {

        $this->backlink = $backlink;

        $this->form = new AppForm($this, 'login');

        $this->form->addText('userName', 'Uživatelské jméno:')
                ->addRule(Form::FILLED, 'Uživatelské jméno musí být vyplněno.');

        $this->form->addPassword('password', 'Přístupové heslo:')
                ->addRule(Form::FILLED, 'Přístupové heslo musí být vyplněno.');

        $this->form->addSubmit('login', 'Přihlásit');


        $this->form->onSubmit[] = array($this, 'FormSubmitted');

        $this->template->form = $this->form;

        if (!isset($this->template->result)) {

            $this->template->result = "";

        }

        $this->user->setAuthenticationHandler(new UsersModel());
    }

    /*
     * Metoda zpracovávající data z přihlašovacího formuláře
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
