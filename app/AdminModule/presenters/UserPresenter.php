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
                $this->flashMessage("You don't have permission to access this object.");
                $this->redirect('Default:');
            }
        }
        else {
            if ((!$this->user->isInRole('Administrator')) && $this->user->getIdentity()->id != $id) {
                $this->flashMessage("You don't have permission to access this object.");
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

        $grid->addColumn('userName', 'User name')->addFilter();
        $grid->addColumn('firstName', 'Name')->addFilter();
        $grid->addColumn('surname', 'Surname')->addFilter();
        $grid->addColumn('email', 'E-mail')->addFilter();
        $grid->addColumn('icq', 'ICQ')->addFilter();
        $grid->addColumn('mobile', 'Mobile')->addFilter();
        $grid->addColumn('active', 'Active')->addSelectboxFilter(array(1 => 'Yes', 0 => 'No'));

        $grid->multiOrder = FALSE; // order by one column only

        $grid['active']->replacement['1'] = 'Yes';
        $grid['active']->replacement['0'] = 'No';

        $grid->displayedItems = array(10, 20, 50, 75, 100, 500, 1000); // roletka pro výběr počtu řádků na stránku

        $grid->addActionColumn('Actions');

        $grid->keyName = 'id';

        $grid->addAction('Activate/Deactivate', 'userActivateChange!', Html::el('span')->setText('Aktivace/Deaktivace'), $useAjax = TRUE);
        $grid->addAction('Edit', 'userChangeRedirect', Html::el('span')->setText('Editovat'), $useAjax = FALSE);
        $grid->addAction('Change password', 'userPasswordChangeRedirect', Html::el('span')->setText('Změnit heslo'), $useAjax = FALSE);
        $grid->addAction('Delete', 'confirmForm:confirmUserDelete!', Html::el('span')->setText('Smazat'), $useAjax = TRUE);

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
            $this->flashMessage("The state of the user was successfully changed.");
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

        return "Are you sure that you want to delete user with username ".$data['userName']."?";
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

        $msg = 'Password must cotain CAPITAL and lower letters and numbers!';

        $this->form->addGroup('Login information');

        $this->form->addText('userName', 'Username:')
                ->addRule(Form::FILLED, 'Username must be filled.')
                ->addRule(Form::MIN_LENGTH, 'Username must have at least %d letters', 4)
                ->addRule(Form::MAX_LENGTH, 'Username can have max %d letters', 20);

        if ($action > 0) {
            $this->form['userName']->setDisabled(true);
        }

        if ($action != 1) {
            $this->form->addPassword('password1', 'Password:')
                    ->addRule(Form::FILLED, 'Password must be filled.')
                    ->addRule(Form::MIN_LENGTH, 'Password must have at least %d letters', 8)
                    ->addRule(Form::MAX_LENGTH, 'Password can have max %d letters', 50)
                    ->addRule(Form::REGEXP, $msg, '/[A-Z]+/')
                    ->addRule(Form::REGEXP, $msg, '/[a-z]+/')
                    ->addRule(Form::REGEXP, $msg, '/[0-9]+/');


            $this->form->addPassword('password2', 'Přístupové heslo (pro kontrolu):')
                    ->addRule(Form::EQUAL, 'Given passwords are not the same.', $this->form['password1']);

            $this->form->addText('navrhHesla', 'Password suggest:');
        }

        if ($action < 2) {
            $this->form->addGroup('Personal information');
            $this->form->addText('firstName', 'Name:')
                    ->addRule(Form::FILLED, 'Name must be filled.');

            $this->form->addText('surname', 'Surname:')
                    ->addRule(Form::FILLED, 'Surname must be filled.');

            $this->form->addText('title', 'Title:')
                    ->addRule(Form::MAX_LENGTH, 'Title can have max %d letters', 10);

            $this->form->addGroup('Contact information');
            $this->form->addText('email', 'E-mail:')
                    ->addRule(Form::FILLED, 'E-mail must be filled.')
                    ->addRule(Form::EMAIL, 'Given e-mail is not valid.');

            $this->form->addText('icq', 'ICQ:')
                    ->addCondition(Form::FILLED)
                    ->addRule(Form::NUMERIC, 'ICQ must be a number.')
                    ->addRule(Form::MAX_LENGTH, 'ICQ can have max %d letters.', 10);

            $this->form->addText('skype', 'Skype:')
                    ->addRule(Form::MAX_LENGTH, 'Skype can have max %d letters.', 50);

            $this->form->addText('mobile', 'Mobile pohone number:')
                    ->addRule(Form::FILLED, 'Mobile phone number must be filled.')
                    ->addRule(Form::NUMERIC, 'Mobile phone number must be a number.')
                    ->addRule(Form::MAX_LENGTH, 'Mobile phone number can have max %d letters', 20);

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

            $this->form->addSubmit('ok', 'Update');
            //            ->onClick[] = array($this, 'OkClicked'); // nebo 'OkClickHandler'
        }
        else {
            $data['navrhHesla'] = UsersModel::genPass();

            // Nastavíme výchozí hodnoty pro formulář
            $this->form->setDefaults($data);

            $this->form->addSubmit('ok', 'Create');
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
                    $this->flashMessage("User was successfully added.");
                    $redirect = true;
                }
                else {
                    $this->flashMessage("User with chosen username is exist. Choose other, please.");
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
                $this->flashMessage("User was successfully updated.");
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
                $this->flashMessage("Password was successfully changed.");
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
            $this->flashMessage("You cannot delete yourself.");
            $this->redirect('User:default');
        }
        else {
            try {
                UsersModel::delUser($id);
                $this->flashMessage("User was successfully deleted.");
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
        $this->flashMessage('Logout was succesful.');
        $this->redirect('Login:');

    }

}
?>