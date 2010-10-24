<?php
/**
 * Rally-Base
 *
 * @copyright  Copyright (c) 2010 Martin Venuš
 * @package    Rally-Base
 */



/**
 * Administrace uživatelů
 *
 * @author     Martin Venuš
 * @package    Rally-Base
 */
class Admin_UserPresenter extends Admin_BasePresenter {
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
     * Metoda, která ověří právo přístupu k dané akci
     * Právo provádět změny u všech uživatelů má pouze Administrator
     * Každý uživatel může změnit své údaje a své heslo
    */
    function access($id=-1) {


        if ($id==-1) {
            if (!$this->user->isInRole('Administrator')) {
                $this->flashMessage("Pouze administrátoři mají přístup k tomuto objektu.");
                $this->redirect('Default:');
            }
        }
        else {
            if ((!$this->user->isInRole('Administrator')) && $this->user->getIdentity()->id != $id) {
                $this->flashMessage("Pouze administrátoři nebo vlastník objektu má přístup k tomuto objektu.");
                $this->redirect('Default:');
            }
        }
    }

    /*
     * Vykreslení stránky pro administraci automobilů
    */
    public function renderDefault() {

        $this->access();

        $this['users']; // získá komponentu

    }

    /*
     * Komponenta datagagrid pro vykreslení tabulky aut
     * @return grid
    */
    protected function createComponentUsers() {

        $grid = new DataGrid;

        $grid->bindDataTable(UsersModel::getUsers());

        $grid->addColumn('userName', 'Uživatelské jméno')->addFilter();
        $grid->addColumn('firstName', 'Jméno')->addFilter();
        $grid->addColumn('surname', 'Přijmení')->addFilter();
        $grid->addColumn('email', 'E-mail')->addFilter();
        $grid->addColumn('icq', 'ICQ')->addFilter();
        $grid->addColumn('mobile', 'Mobilní telefon')->addFilter();
        $grid->addColumn('active', 'Aktivní')->addSelectboxFilter(array(1 => 'Yes', 0 => 'No'));

        $grid->multiOrder = FALSE; // order by one column only

        $grid['active']->replacement['1'] = 'Ano';
        $grid['active']->replacement['0'] = 'Ne';

        $grid->displayedItems = array(10, 20, 50, 75, 100, 500, 1000); // roletka pro výběr počtu řádků na stránku

        $grid->addActionColumn('Actions');

        $grid->keyName = 'id';

        $grid->addAction('Aktivovat/Deaktivovat', 'userActivateChange!', Html::el('span')->setText('Aktivace/Deaktivace'), $useAjax = TRUE);
        $grid->addAction('Editovat', 'userChangeRedirect', Html::el('span')->setText('Editovat'), $useAjax = FALSE);
        $grid->addAction('Změnit heslo', 'userPasswordChangeRedirect', Html::el('span')->setText('Změnit heslo'), $useAjax = FALSE);
        $grid->addAction('Smazat', 'confirmForm:confirmUserDelete!', Html::el('span')->setText('Smazat'), $useAjax = TRUE);

        return $grid;
    }

    public function actionUserChangeRedirect($id) {
        $this->redirect('userChange', 1, $id);
    }

    public function actionUserPasswordChangeRedirect($id) {
        $this->redirect('userChange', 2, $id);
    }

    /*
     * Funkce invertuje sloupec active v DB
     * @param $id id záznamu, který má být změněn
    */
    public function handleUserActivateChange($id) {

        $this->access();

        try {
            BaseModel::activeChange('user', $id);
            dibi::query('COMMIT');
            $this->flashMessage("Stav uživatele byl úspěšně změněn.");
        }
        catch (Exception $e) {
            dibi::query('ROLLBACK');
            Debug::processException($e);
            $this->flashMessage(ERROR_MESSAGE . " Error description: " .$e->getMessage(), 'error');
        }

        $this->invalidateControl('users');
    }

    /*
    * Komponenta pro potvrzení akce smazání záznamu
    */
    function createComponentConfirmForm() {
        $form = new ConfirmationDialog();

        $form->addConfirmer(
                'userDelete',
                array($this, 'userDelete'),
                array($this, 'questionUserDelete')
        );

        return $form;
    }

    /*
    * Otázka pro smazání
    */
    function questionUserDelete($dialog, $params) {

        $data = BaseModel::getItem('user', $params['id']);

        return "Opravdu chcete smazat uživatele s uživatelským jménem ".$data['userName']."?";
    }

    /*
     * Metoda, která vykreslí formulář pro vytvoření event. úpravy uživatele
     * @param $action new=0, edit=1, password=2 - určuje typ akce
     * @param $id id uživatele
    */
    public function actionUserChange($akce=0, $id=-1) {
        $this->access($id);

        //kvůli Nette - action je vyhrazeno
        $action = $akce;

        $this->verifyUser();

        $this->form = new AppForm($this, 'userChange');

        //TODO: Definovat zprávu
        //$this->form->addProtection($this->formProtectedMessage);

        $msg = 'Heslo musí obsahovat VELKÁ a malá písmena a číslice!';

        $this->form->addGroup('Přihlašovací informace');

        $this->form->addText('userName', 'Uživatelské jméno:')
                ->addRule(Form::FILLED, 'Uživatelské jméno musí být vyplněno.')
                ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít alespoň %d znaky.', 4)
                ->addRule(Form::MAX_LENGTH, 'Uživatelské jméno může mít nejvýše %d znaků.', 20);

        if ($action > 0) {
            $this->form['userName']->setDisabled(true);
        }

        if ($action != 1) {
            $this->form->addPassword('password1', 'Heslo:')
                    ->addRule(Form::FILLED, 'Heslo musí být vyplněno.')
                    ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 8)
                    ->addRule(Form::MAX_LENGTH, 'Heslo může mít nejvýše %d znaků', 50)
                    ->addRule(Form::REGEXP, $msg, '/[A-Z]+/')
                    ->addRule(Form::REGEXP, $msg, '/[a-z]+/')
                    ->addRule(Form::REGEXP, $msg, '/[0-9]+/');


            $this->form->addPassword('password2', 'Přístupové heslo (pro kontrolu):')
                    ->addRule(Form::EQUAL, 'Zadaná hesla se neshodují.', $this->form['password1']);

            $this->form->addText('navrhHesla', 'Navrhované heslo:');
        }

        if ($action < 2) {
            $this->form->addGroup('Osobní informace');
            $this->form->addText('firstName', 'Jméno:')
                    ->addRule(Form::FILLED, 'Jméno musí být vyplněno.');

            $this->form->addText('surname', 'Přijmení:')
                    ->addRule(Form::FILLED, 'Přijmení musí být vyplněno.');

            $this->form->addText('title', 'Titul:')
                    ->addRule(Form::MAX_LENGTH, 'Titul může mít nejvýše %d znaků.', 10);

            $this->form->addGroup('Kontaktní informace');
            $this->form->addText('email', 'E-mail:')
                    ->addRule(Form::FILLED, 'E-mail musí být vyplněn.')
                    ->addRule(Form::EMAIL, 'Zadaný e-mail není platný.');

            $this->form->addText('icq', 'ICQ:')
                    ->addCondition(Form::FILLED)
                    ->addRule(Form::NUMERIC, 'ICQ musí být číslo.')
                    ->addRule(Form::MAX_LENGTH, 'ICQ může mít nejvýše %d znaků.', 10);

            $this->form->addText('skype', 'Skype:')
                    ->addRule(Form::MAX_LENGTH, 'Skype může mát nejvýše %d znaků.', 50);

            $this->form->addText('mobile', 'Mobilní telefon:')
                    ->addCondition(Form::FILLED)
                    ->addRule(Form::NUMERIC, 'Mobilní telefon musí být číslo.')
                    ->addRule(Form::MAX_LENGTH, 'Mobilní telefon může mít nejvýše %d znaků.', 20);

            $roles = UsersModel::getRoles();

            // Zobrazujeme pouze tehdy je-li uživatel administrátor - aby si uživatel sám nemohl měnit skupinu
            if ($this->user->isInRole('Administrator')) {
                $this->form->addGroup('Přístupová práva (může být vybráno více skupin)');
                $this->form->addMultiSelect('prava', 'Přístupová práva:', $roles, 2);
            }
        }

        $this->form->onSubmit[] = array($this, 'UserFormSubmitted');

        $this->form->addHidden('action')->setValue($action);

        if ($action > 0) {
            $data = UsersModel::getUser($id);

            $data['navrhHesla'] = UsersModel::genPass();

            // Nastavíme výchozí hodnoty pro formulář
            $this->form->setDefaults($data);

            $this->form->addHidden('id')->setValue($id);

            $this->form->addSubmit('ok', 'Aktualizovat');
            //            ->onClick[] = array($this, 'OkClicked'); // nebo 'OkClickHandler'
        }
        else {
            $data['navrhHesla'] = UsersModel::genPass();

            // Nastavíme výchozí hodnoty pro formulář
            $this->form->setDefaults($data);

            $this->form->addSubmit('ok', 'Vytvořit');
            //            ->onClick[] = array($this, 'OkClicked'); // nebo 'OkClickHandler'
        }

        $this->template->form = $this->form;

    }

    /*
     * Zpracování odeslaného formuláře
     * @param $form data z formuláře
     * Zpracování se liší podle akce
    */
    function UserFormSubmitted(Form $form) {

        $data = $form->getValues(); // vezmeme data z formuláře

        $action = $form['action']->getValue();

        if ($action == 0) {
            //vkládáme nového uživatele
            try {

                //jestliže neexistuje uživatel s požadovaným uživatelským jménem
                if (!UsersModel::getUserByUserName($data['userName'])) {
                    UsersModel::createUser($data);
                    dibi::query('COMMIT');
                    $this->flashMessage("Uživatel byl úspěšně vytvořen.");
                    $redirect = true;
                }
                else {
                    $this->flashMessage("Uživatel se zadaným uživatelským jménem již existuje. Vyberte prosím jiné uživatelské jméno.");
                    $redirect = false;
                }
            }
            catch (Exception $e) {
                dibi::query('ROLLBACK');
                Debug::processException($e);
                $this->flashMessage(ERROR_MESSAGE . " Error description: " .$e->getMessage(), 'error');
            }

            if ($redirect) {
                $this->redirect('User:default');
            }

        }
        elseif($action == 1) {

            //editujeme uživatele
            try {
                UsersModel::editUser($data);
                $this->flashMessage("Užovatel byl úspěšně aktualizován.");
                dibi::query('COMMIT');
            }
            catch (Exception $e) {
                dibi::query('ROLLBACK');
                Debug::processException($e);
                $this->flashMessage(ERROR_MESSAGE . " Error description: " .$e->getMessage(), 'error');
            }

            if ($this->user->isInRole('Administrator')) {
                $this->redirect('User:default');
            }
            else {
                $this->redirect('User:default');
            }
        }
        elseif($action == 2) {
            //měníme pouze heslo
            try {
                UsersModel::editPassword($data);
                $this->flashMessage("Heslo bylo úspěšně změněno.");
                dibi::query('COMMIT');
            }
            catch (Exception $e) {
                dibi::query('ROLLBACK');
                Debug::processException($e);
                $this->flashMessage(ERROR_MESSAGE . " Error description: " .$e->getMessage(), 'error');
            }

            if ($this->user->isInRole('Administrator')) {
                $this->redirect('User:default');
            }
            else {
                $this->redirect('Default:');
            }

        }

    }

    /*
     * Metoda smaže uživatele se zadaným id
     * Nepovolí smazat sama sebe
     * @param $id id mazaného uživatele
     * @param $return informace z dotazu, zda chce uživatel akci skutečně provést
     * 0=informace ještě nezískána, 1=chce, 2=nechce
    */
    public function actionUserDelete($id) {

        $this->access();

        $pom = $this->user->getIdentity();

        //jestliže chceme smazat sami sebe - vyhodíme hlášku a přesměrujeme
        if ($pom->id == $id) {
            $this->flashMessage("Nemůžete smazat sám sebe.");
            $this->redirect('User:default');
        }
        else {
            try {
                UsersModel::delUser($id);
                $this->flashMessage("Uživatel byl úspěšně smazán.");
                dibi::query('COMMIT');
            }
            catch (Exception $e) {
                dibi::query('ROLLBACK');
                Debug::processException($e);
                $this->flashMessage(ERROR_MESSAGE . " Error description: " .$e->getMessage(), 'error');
            }

            $this->redirect('User:default');
        }

    }

    /*
     * Odhlásí uživatele
     * Nastaví zprávu
     * Smaže identitu
     * Přesměruje na přihlášení
    */
    public function actionLogout() {

        // odhlásí uživatele a zároveň smaže identitu
        $this->user->logout(TRUE);
        $this->flashMessage('Odhlášení bylo úspěšné.');
        $this->redirect('Login:');

    }

}
?>