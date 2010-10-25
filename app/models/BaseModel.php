<?php
/**
 * Rally-Base
 *
 * @copyright  Copyright (c) 2010 Martin Venuš
 * @package    Rally-Base
 */



/**
 * Základní model
 *
 * @author     Martin Venuš
 * @package    Rally-Base
 */
class BaseModel {
    /*
     * Získá jeden záznam z databáze
     * @param source tabulka, ze které se bude vybírat
     * @param id ID požadovaného záznamu
    */
    public static function getItem($source, $id) {
        $result = dibi::dataSource('SELECT * FROM %n WHERE id = %i', $source, $id);

        $data = $result->fetchAll();

        if (count($data) > 0) {
            return $data[0];
        }
        else {
            return null;
        }
    }

    /*
     * Invertuje sloupec v databázi (1=>0, 0=>1)
     * @param source tabulka
     * @param id ID záznamu
     * @param column sloupec
    */
    public static function activeChange($source, $id, $column = 'active') {
        $result = dibi::query('SELECT %n FROM %n WHERE id = %i', $column, $source, $id);
        $activate = $result->fetchSingle();

        if ($activate == 0) {
            $update = 1;
        }
        elseif ($activate == 1) {
            $update = 0;
        }
        else {
            $update = 0;
        }

        dibi::query('UPDATE %n SET %n = %i WHERE id = %i', $source, $column, $update, $id);
    }

    /*
     * Smaže záznam podle ID
     * @param source tabulka
     * @param id ID záznamu
    */
    public static function deleteItem($source, $id) {
        dibi::query('DELETE FROM %n WHERE id = %i', $source, $id);
    }
}