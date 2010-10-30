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
 * Default class
 *
 */
class Admin_DefaultPresenter extends Admin_BasePresenter
{

	public function actionDefault()
	{
		$this->redirect('Ticket:');
	}

}
