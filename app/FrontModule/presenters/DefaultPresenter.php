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
 * Default presenter
 *
 */
class Front_DefaultPresenter extends Front_BasePresenter {

    /** @var Form */
    protected $form;


    /*
     * Metoda pro přidání tiketu
     */

    function actionDefault() {

        $data['ipAddress'] = $_SERVER['SERVER_ADDR'];

        $departs = UsersModel::getAllPublicDepartments();

        $this->form = new AppForm($this, 'Ticket');

        $this->form->addText('name', 'Jméno a příjmení:')
                ->addRule(Form::FILLED, 'Jméno musí být vyplněno.');

        $this->form->addText('email', 'E-mail:')
                ->addRule(Form::FILLED, 'E-mail musí být vyplněn.')
                ->addRule(Form::EMAIL, 'Zadaný e-mail není platný.');

        $this->form->addText('mobile', 'Telefon:')
                ->addRule(Form::NUMERIC, 'Telefon musí být číslo.')
                ->addRule(Form::MAX_LENGTH, 'Telefon může mít nejvýše %d znaků.', 20);

        $this->form->addSelect('departmentId', 'Oddělení:', $departs)
                ->addRule(Form::FILLED, 'Zadejte oddělení.');

        $this->form->addText('subject', 'Předmět ', 48)
                ->addRule(Form::FILLED, 'Předmět musí být vyplněn.')
                ->addRule(Form::MAX_LENGTH, 'Předmět může mít nejvýše %d znaků.', 255);

        $this->form->addTextarea('ticketMessage', 'Zpráva:')
                ->addRule(Form::FILLED, 'Uveďte zprávu.');

        $this->form->addSubmit('ok', 'Vytvořit tiket');

        $this->form->setDefaults($data);

        //Define hidden values
        $this->form->addHidden('staffId', NULL);
        $this->form->addHidden('priority', 1);
        $this->form->addHidden('status', 'Otevřený');
        $this->form->addHidden('source', 'web');
        $this->form->addHidden('closed', 0);
        $this->form->addHidden('created', time());
        $this->form->addHidden('updated', time());
        $this->form->addHidden('ip', $data['ipAddress']);

        //Send to template
        $this->form->onSubmit[] = array($this, 'addTicketFormProcess');
        $this->template->form = $this->form;
    }

    /*
     * Zpracování odeslaného formuláře pro přidání tiketu
     * @param $form data z formuláře
     */

    function addTicketFormProcess(Form $form) {

        $data = $form->getValues(); // vezmeme data z formuláře

        $data['time'] = time();

        $data['type'] = 0;

        $data['tid'] = Admin_TicketPresenter::genTicketID($data['departmentId']);

        try {
            TicketsModel::addTicket($data);
            dibi::query('COMMIT');
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            Debug::processException($e);
            $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
        }

        $this->flashMessage("Váš tiket byl úspěšně přidán.");

        $this->redirect('Default:sent');
    }

    

}
