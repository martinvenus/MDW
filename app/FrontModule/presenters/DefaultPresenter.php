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
class Front_DefaultPresenter extends Front_BasePresenter
{

	public function renderDefault()
	{
		$this->template->message = 'We hope you enjoy this framework!';
	}

}
