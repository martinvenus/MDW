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
 * Ticket managment
 *
 */
class Admin_TicketPresenter extends Admin_BasePresenter {

    /** @var Form */
    protected $form;

    /*
     * Zavolá funkci verifyUser, která ověří, zda je uživatel přihlášen
     * V případě že není -> přesměruje na přihlášení
     */

    public function startup() {

        parent::startup();

        $this->verifyUser();
    }

    /*
     * Defaultní akce presenteru
     */

    public function actionDefault() {

        //$this->access();
        $this['newTickets']; // získá komponentu
    }

    /*
     * Vykreslení stránky s tickety aktuálního uživatele
     */

    public function actionMyTickets() {

        //$this->access();

        $this['myTickets']; // získá komponentu
    }

    /*
     * Vykreslení stránky s tickety aktuálního uživatele
     */

    public function actionClosedTickets() {

        //$this->access();

        $this['closedTickets']; // získá komponentu
    }

    /*
     * Komponenta datagagrid pro vykreslení tabulky ticketů aktuálního uživatele
     * @return grid
     */

    protected function createComponentMyTickets() {

        $grid = new DataGrid;
        $grid->bindDataTable(TicketsModel::getMyTickets($this->user->getIdentity()->id));

        $grid->addColumn('ticketId', 'Tiket')->addFilter();
        $grid->addColumn('priority', 'Priorita')->addFilter();
        $grid->addColumn('subject', 'Předmět')->addFilter();
        $grid->addColumn('name', 'Autor')->addFilter();
        $grid->addColumn('status', 'Status')->addSelectboxFilter(array('Uzavřený' => 'Uzavřený', 'Otevřený' => 'Otevřený'));
        $grid->addColumn('updated', 'Datum a čas')->addFilter();

        $grid->addActionColumn('Akce');

        $grid->addAction('Zobrazit', 'showTicket', Html::el('span')->setText('Zobrazit'), $useAjax = FALSE);

        $grid->multiOrder = FALSE; // order by one column only

        $grid->displayedItems = array(10, 20, 50, 75, 100, 500, 1000); // roletka pro výběr počtu řádků na stránku

        $grid->keyName = 'id';

        $grid['updated']->formatCallback[] = array($this, 'updatedFormat');

        return $grid;
    }

    /*
     * Komponenta datagagrid pro vykreslení tabulky nepřiřazených tiketů
     * @return grid
     */

    protected function createComponentNewTickets() {

        $grid = new DataGrid;
        $grid->bindDataTable(TicketsModel::getNewTickets(UsersModel::getDepartment($this->user->getIdentity()->id)));

        $grid->addColumn('ticketId', 'Tiket')->addFilter();
        $grid->addColumn('priority', 'Priorita')->addFilter();
        $grid->addColumn('subject', 'Předmět')->addFilter();
        $grid->addColumn('name', 'Autor')->addFilter();
        $grid->addColumn('status', 'Status')->addSelectboxFilter(array('Uzavřený' => 'Uzavřený', 'Otevřený' => 'Otevřený'));
        $grid->addColumn('updated', 'Datum a čas')->addFilter();

        $grid->addActionColumn('Akce');

        $grid->addAction('Zobrazit', 'showTicket', Html::el('span')->setText('Zobrazit'), $useAjax = FALSE);

        $grid->multiOrder = FALSE; // order by one column only

        $grid->displayedItems = array(10, 20, 50, 75, 100, 500, 1000); // roletka pro výběr počtu řádků na stránku

        $grid->keyName = 'id';

        $grid['updated']->formatCallback[] = array($this, 'updatedFormat');

        return $grid;
    }

    /*
     * Komponenta datagagrid pro vykreslení tabulky nepřiřazených tiketů
     * @return grid
     */

    protected function createComponentClosedTickets() {

        $grid = new DataGrid;
        $grid->bindDataTable(TicketsModel::getClosedTickets(UsersModel::getDepartment($this->user->getIdentity()->id)));

        $grid->addColumn('ticketId', 'Tiket')->addFilter();
        $grid->addColumn('priority', 'Priorita')->addFilter();
        $grid->addColumn('subject', 'Předmět')->addFilter();
        $grid->addColumn('name', 'Autor')->addFilter();
        $grid->addColumn('status', 'Status')->addSelectboxFilter(array('Uzavřený' => 'Uzavřený', 'Otevřený' => 'Otevřený'));
        $grid->addColumn('updated', 'Datum a čas')->addFilter();

        $grid->addActionColumn('Akce');

        $grid->addAction('Zobrazit', 'showTicket', Html::el('span')->setText('Zobrazit'), $useAjax = FALSE);

        $grid->multiOrder = FALSE; // order by one column only

        $grid->displayedItems = array(10, 20, 50, 75, 100, 500, 1000); // roletka pro výběr počtu řádků na stránku

        $grid->keyName = 'id';

        $grid['updated']->formatCallback[] = array($this, 'updatedFormat');

        return $grid;
    }

    /*
     * Metoda formátování data do tvaru d.m.Y H:i
     * @param $date Datum v unixtime
     */

    function updatedFormat($date) {
        if ($date > 0) {
            return Date('d.m.Y H:i', $date);
        } else {
            return null;
        }
    }

    /*
     * Metoda pro zobrazení detailů tiketu
     * @param $id ID tiketu
     */

    public function actionShowTicket($id) {
        $this->template->detaily = TicketsModel::getTicketDetails($id);
        $this->template->zpravy = TicketsModel::getTicketMessages($id);
        $this->template->zamestnanci = UsersModel::getStaffNames();
        $this->template->registerHelper('blogUrl', array('BlogHelpers', 'blogUrl'));
    }

    /*
     * Metoda pro předání tiketu kolegovi
     * @param $id ID tiketu
     */

    public function actionForwardTicket($id) {

        $this->form = new AppForm($this, 'fwdTicket');

        $this->form->addHidden('tiket', $id);

        $this->form->addSelect('colleague', "Zvolte kolegu:", UsersModel::getMyColleagues(UsersModel::getDepartment($this->user->getIdentity()->id)));

        $this->form->addSubmit('forward', 'Předat');

        $this->form->onSubmit[] = array($this, 'ForwardFormProcess');

        $this->template->form = $this->form;
    }

    /*
     * Zpracování odeslaného formuláře pro předání tiketu kolegovi
     * @param $form data z formuláře
     */

    function ForwardFormProcess(Form $form) {

        $data = $form->getValues(); // vezmeme data z formuláře

        $data['type'] = 3; // 3 = systémová zpráva

        $data['comment'] = "Tiket předán od uživatele " . $this->user->getidentity()->firstName . " " . $this->user->getidentity()->surname . " uživateli  " . UsersModel::getStaffName($data['colleague']) . ".";

        $data['time'] = time();

        $data['name'] = "System";


        try {
            TicketsModel::forwardTicket($data);
            dibi::query('COMMIT');
            $this->flashMessage("Tiket byl úspěšně předán kolegovi.");
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            Debug::processException($e);
            $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
        }

        $this->redirect('Ticket:myTickets');
    }

    /*
     * Metoda pro předání tiketu do jiného oddělení
     * @param $id ID tiketu
     */

    public function actionChangeDepartment($id) {

        $this->form = new AppForm($this, 'chngDepartment');

        $this->form->addHidden('tiket', $id);

        $this->form->addSelect('department', "Zvolte oddělení:", UsersModel::getAllDepartments());

        $this->form->addSubmit('forward', 'Předat');

        $this->form->onSubmit[] = array($this, 'DepartmentFormProcess');

        $this->template->form = $this->form;
    }

    /*
     * Zpracování odeslaného formuláře pro předání di jiného oddělení
     * @param $form data z formuláře
     */

    function DepartmentFormProcess(Form $form) {

        $data = $form->getValues(); // vezmeme data z formuláře

        $data['type'] = 3; // 3 = systémová zpráva

        $data['comment'] = "Tiket předán uživatelem " . $this->user->getidentity()->firstName . " " . $this->user->getidentity()->surname . " z \"" . UsersModel::getDepartmentName(UsersModel::getDepartment($this->user->getIdentity()->id)) . "\" do \"" . UsersModel::getDepartmentName($data['department']) . "\".";

        $data['time'] = time();

        $data['name'] = "System";

        try {
            TicketsModel::changeDepartment($data);
            dibi::query('COMMIT');
            $this->flashMessage("Tiket byl úspěšně předán danému oddělení.");
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            Debug::processException($e);
            $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
        }

        $this->redirect('Ticket:');
    }

    /*
     * Metoda pro otevření/uzavření tiketu
     * @param $id ID tiketu
     * @param $status Nastavení hodnoty 0 - otevřít, 1 - uzavřít
     */

    public function actionChangeClosed($id, $status) {

        $data['type'] = 3; // 3 = systémová zpráva

        $data['tiket'] = $id;

        if ($status == 1) {
            $data['comment'] = "Tiket uzavřen uživatelem " . $this->user->getidentity()->firstName . " " . $this->user->getidentity()->surname . ".";
            $data['closed'] = 1;
            $data['status'] = "Uzavřený";
        } else {
            $data['comment'] = "Tiket otevřen uživatelem " . $this->user->getidentity()->firstName . " " . $this->user->getidentity()->surname . ".";
            $data['closed'] = 0;
            $data['status'] = "Otevřený";
        }

        $data['time'] = time();

        $data['name'] = "System";

        try {
            TicketsModel::changeClosed($data);
            dibi::query('COMMIT');
            $this->flashMessage("Status tiketu byl úspěšně nastaven.");
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            Debug::processException($e);
            $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
        }

        $this->redirect('Ticket:showTicket', $id);
    }

    /*
     * Metoda pro přijetí tiketu
     * @param $id ID tiketu
     */

    public function actionTakeTicket($id) {

        $data['type'] = 3; // 3 = systémová zpráva

        $data['comment'] = "Tiket byl přijat uživatelem " . $this->user->getidentity()->firstName . " " . $this->user->getidentity()->surname . ".";

        $data['time'] = time();

        $data['name'] = "System";

        try {
            TicketsModel::setTicketStaff($id, $this->user->getIdentity()->id, $data);
            dibi::query('COMMIT');
            $this->flashMessage("Tiket byl úspěšně přijat.");
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
            $this->redirect('Ticket:');
        }

        $this->redirect('Ticket:showTicket', $id);
    }

    /*
     * Metoda pro vrácení tiketu
     * @param $id ID tiketu
     */

    public function actionReturnTicket($id) {

        $data['type'] = 3; // 3 = systémová zpráva

        $data['comment'] = "Uživatel " . $this->user->getidentity()->firstName . " " . $this->user->getidentity()->surname . " se vzdal tiketu.";

        $data['time'] = time();

        $data['name'] = "System";

        try {
            TicketsModel::setTicketStaff($id, NULL, $data);
            dibi::query('COMMIT');
            $this->flashMessage("Vzdal jste se tiketu.");
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
            $this->redirect('Ticket:');
        }

        $this->redirect('Ticket:showTicket', $id);
    }

    /*
     * Metoda pro přidání odpovědi na tiket
     * @param $id ID tiketu
     * @param $internal Příznak zda se jedná o interní poznámku (neodešle mail zadavateli)
     */

    public function actionAddReply($id, $internal) {

        $this->form = new AppForm($this, 'sendReply');

        $this->form->addHidden('tiket', $id);

        $this->form->addHidden('internal', $internal);

        $this->form->addTextArea('message', 'Odpověď/komentář:', 10);

        $this->form->addSubmit('send', 'Odeslat');

        $this->form->onSubmit[] = array($this, 'ReplyFormProcess');

        $this->template->form = $this->form;
    }

    /*
     * Zpracování odeslaného formuláře pro přidání odpovědi nebo interní poznámky k tiketu
     * @param $form data z formuláře
     */

    function ReplyFormProcess(Form $form) {

        $data = $form->getValues(); // vezmeme data z formuláře        

        $data['time'] = time();

        $data['name'] = $this->user->getidentity()->firstName . " " . $this->user->getidentity()->surname;

        if ($data['internal'] == 1) {
            $data['type'] = 2;
        } else {
            $data['type'] = 1;
        }

        try {
            TicketsModel::addReply($data);
            dibi::query('COMMIT');
            if ($data['internal'] == 0) {
                // TODO: Odeslání mailu
                $this->flashMessage("Odeslání mailu autorovi tiketu bude doprogramováno později.");
            }
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            Debug::processException($e);
            $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
        }

        $this->flashMessage("Odpověď/interní poznámka byla úspěšně uložena.");

        $this->redirect('Ticket:showTicket', $data['tiket']);
    }

    /*
     * Metoda pro přidání tiketu
     */

    function actionAddTicket() {

        $data['name'] = $this->user->getidentity()->firstName . " " . $this->user->getidentity()->surname;
        $data['ipAddress'] = $_SERVER['SERVER_ADDR'];

        $departs = UsersModel::getAllDepartments();

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
        $this->form->addHidden('staffId', NULL); //TODO: Zjistit jak definovat staffId
        $this->form->addHidden('priority', 5); //TODO: Highest priority if admin, not defined if user
        $this->form->addHidden('status', 'Otevřený');
        $this->form->addHidden('source', 'admin');
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

        $data['tid'] = $this->genTicketID($data['departmentId']);
        
        try {
            TicketsModel::addTicket($data);
            dibi::query('COMMIT');
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            Debug::processException($e);
            $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
        }

        $this->flashMessage("Váš tiket byl úspěšně přidán.");

        $this->redirect('Ticket:');
    }

    public static function genTicketID($department) {

        do {

            $cislice = "1234567890";
            $date = time();

            $tid = '';
            for ($i = 0; $i < 5; $i++) {
                $chars = $cislice;
                $numChars = strlen($chars);
                $tid[$i] = substr($chars, mt_rand(1, $numChars) - 1, 1);
            }

            // Zamícháme pole
            shuffle($tid);
            $tidfin = '';
            for ($i = 0; $i < count($tid); $i++) {
                $tidfin.=$tid[$i];
            }
            $ticketID = $department . '-' . $date . '-' . $tidfin;
        }//check in DB if such ticketID exists
        
        while (TicketsModel::checkTicketId($ticketID) > 0);

        return $ticketID;

    }

}

?>