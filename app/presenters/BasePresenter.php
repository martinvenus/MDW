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
abstract class BasePresenter extends Presenter {

    public $oldLayoutMode = FALSE;

    public function startup() {
        //Debug::timer();

        parent::startup();

        dibi::query('SET AUTOCOMMIT=0');
    }

}
