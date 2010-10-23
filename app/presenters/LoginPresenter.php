<?php

/**
 * My NApplication
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */



/**
 * Login / logout presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class LoginPresenter extends BasePresenter
{


	/**
	 * Login form component factory.
	 * @return mixed
	 */
	protected function createComponentLoginForm()
	{
		$form = new NAppForm;
		$form->addText('username', 'Username:')
			->addRule(NForm::FILLED, 'Please provide a username.');

		$form->addPassword('password', 'Password:')
			->addRule(NForm::FILLED, 'Please provide a password.');

		$form->addCheckbox('remember', 'Remember me on this computer');

		$form->addSubmit('login', 'Login');

		$form->onSubmit[] = callback($this, 'loginFormSubmitted');
		return $form;
	}



	public function loginFormSubmitted($form)
	{
		try {
			$values = $form->values;
			if ($values['remember']) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values['username'], $values['password']);
			$this->redirect('Homepage:');

		} catch (NAuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

}
