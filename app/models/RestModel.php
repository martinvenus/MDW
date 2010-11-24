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
 * Model for REST
 *
 */
class RestModel extends BaseModel {
    public static function verifyContentType($requiredContentType, $givenContentType){
        trim($requiredContentType);
        trim($givenContentType);

        if (strcmp($requiredContentType, $givenContentType) == 0){
            return true;
        }

        return false;
    }
}

