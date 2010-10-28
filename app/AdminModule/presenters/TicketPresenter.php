<?php

/**
 * Správa ticketů
 *
 * @author     Martin Venuš
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

    public function actionDefault() {

        //$this->access();
        $this['newTickets']; // získá komponentu
    }

    /*
     * Vykreslení stránky s tickety
     */

    public function actionMyTickets() {

        //$this->access();

        $this['myTickets']; // získá komponentu
    }

    /*
     * Komponenta datagagrid pro vykreslení tabulky mojich ticketů
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
        $grid->addColumn('updated', 'Časová značka')->addFilter();

        $grid->addActionColumn('Akce');

        $grid->addAction('Zobrazit', 'showTicket', Html::el('span')->setText('Zobrazit'), $useAjax = FALSE);

        $grid->multiOrder = FALSE; // order by one column only

        $grid->displayedItems = array(10, 20, 50, 75, 100, 500, 1000); // roletka pro výběr počtu řádků na stránku

        $grid->keyName = 'id';

        $grid['updated']->formatCallback[] = array($this, 'updatedFormat');

        return $grid;
    }

    /*
     * Komponenta datagagrid pro vykreslení tabulky mojich ticketů
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
        $grid->addColumn('updated', 'Časová značka')->addFilter();

        $grid->addActionColumn('Akce');

        $grid->addAction('Zobrazit', 'showTicket', Html::el('span')->setText('Zobrazit'), $useAjax = FALSE);

        $grid->multiOrder = FALSE; // order by one column only

        $grid->displayedItems = array(10, 20, 50, 75, 100, 500, 1000); // roletka pro výběr počtu řádků na stránku

        $grid->keyName = 'id';

        $grid['updated']->formatCallback[] = array($this, 'updatedFormat');

        return $grid;
    }

    function updatedFormat($date) {
        if ($date > 0) {
            return Date('d.m.Y H:i', $date);
        } else {
            return null;
        }
    }

    public function actionShowTicket($id) {
        $this->template->detaily = TicketsModel::getTicketDetails($id);
        $this->template->zpravy = TicketsModel::getTicketMessages($id);
        $this->template->zamestnanci = UsersModel::getStaffNames();
        $this->template->registerHelper('blogUrl', array('BlogHelpers', 'blogUrl'));
    }

    public function actionForwardTicket($id) {

        $this->form = new AppForm($this, 'fwdTicket');

        $this->form->addHidden('tiket', $id);

        $this->form->addSelect('colleague', "Zvolte kolegu:", UsersModel::getMyColleagues(UsersModel::getDepartment($this->user->getIdentity()->id)));

        $this->form->addSubmit('forward', 'Předat');

        $this->form->onSubmit[] = array($this, 'ForwardFormProcess');

        $this->template->form = $this->form;
    }

    /*
     * Zpracování odeslaného formuláře
     * @param $form data z formuláře
     */

    function ForwardFormProcess(Form $form) {

        $data = $form->getValues(); // vezmeme data z formuláře

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

    public function actionChangeDepartment($id) {

        $this->form = new AppForm($this, 'chngDepartment');

        $this->form->addHidden('tiket', $id);        

        $this->form->addSelect('department', "Zvolte oddělení:", UsersModel::getAllDepartments());

        $this->form->addSubmit('forward', 'Předat');

        $this->form->onSubmit[] = array($this, 'DepartmentFormProcess');

        $this->template->form = $this->form;
    }

    /*
     * Zpracování odeslaného formuláře
     * @param $form data z formuláře
     */

    function DepartmentFormProcess(Form $form) {

        $data = $form->getValues(); // vezmeme data z formuláře

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

    public function actionTakeTicket($id) {

        $data['comment'] = "Tiket byl přijat uživatelem " . $this->user->getidentity()->firstName . " " . $this->user->getidentity()->surname . ".";

        $data['time'] = time();

        $data['name'] = "System";

        try {
            TicketsModel::setTicketStaff($id, $this->user->getIdentity()->id, $data);
            dibi::query('COMMIT');
            $this->flashMessage("Tcket byl úspěšně přijat.");
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
            $this->redirect('Ticket:');
        }

        $this->redirect('Ticket:myTickets');
    }

}

?>