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
 * Base class for all application presenters.
 *
 */
abstract class Front_BasePresenter extends BasePresenter
{
	public $oldLayoutMode = FALSE;

    public function startup() {
        parent::startup();
    }

}
