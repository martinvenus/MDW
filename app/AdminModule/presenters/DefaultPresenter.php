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
class Admin_DefaultPresenter extends Admin_BasePresenter
{

	public function renderDefault()
	{
		$this->template->message = 'We hope you enjoy this framework!';
	}

}
