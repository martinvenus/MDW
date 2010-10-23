<?php

/**
 * My NApplication
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class Admin_LoginPresenter extends Admin_BasePresenter {

    /** @var Form */
    protected $form;

        /*
     * Konstruktor
     */
    protected function startup()
    {

        parent::startup();

    }



    public function actionDefault() {

        $this->form = new NAppForm($this, 'login');

        $this->form->addText('userName', 'Uživatelské jméno:')
        ->addRule(NForm::FILLED, 'Uživatelské jméno musí být vyplněno.');

        $this->form->addPassword('password', 'Přístupové heslo:')
        ->addRule(NForm::FILLED, 'Přístupové heslo musí být vyplněno.');
        //->addRule(Form::MIN_LENGTH, 'Heslo musí být minimálně %d znaků', 12);


        $this->form->addSubmit('login', 'Přihlásit');


        //$this->form->onSubmit[] = array($this, 'FormSubmitted');

        $this->template->form = $this->form;

    }

}
