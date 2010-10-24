<?php

/**
 * My NApplication
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Presenter {

    public $oldLayoutMode = FALSE;

    public function startup() {
        //Debug::timer();

        parent::startup();

        dibi::query('SET AUTOCOMMIT=0');
    }

}
