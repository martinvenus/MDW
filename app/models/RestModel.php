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

    /*
     * Metoda ověří, zda přijatá data jsou požadovaného typu
     * @param requiredContentType požadovaný typ dokumentu
     * @param givenContentType přijatý typ dokumentu
     */
    public static function verifyContentType($requiredContentType, $givenContentType) {
        trim($requiredContentType);
        trim($givenContentType);

        if (strcmp($requiredContentType, $givenContentType) == 0) {
            return true;
        }

        return false;
    }

    /*
     * Metoda ověří, zda je zadaný klíč platný
     * @param key API klíč
     */
    public static function verifyAPIkey($key) {
        $result = dibi::query('SELECT id FROM api WHERE `key`=%s AND active = 1', $key);
        $count = count($result);

        if ($count > 0) {
            return true;
        }

        throw new AuthenticationException('Invalid API Key');
    }

}

