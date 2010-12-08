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
 * Base model
 *
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

    /**
     * Metoda pro testování výkonu aplikace
     * @param int Velikost pole, které bude naplněno náhodnými hodnotami a seřazeno
     */
    public static function sortRandomArray($size = 10000){
        $array = array();

        for($i = 0; $i < $size; $i++) {
            $array[$i] = rand(0, PHP_INT_MAX);
        }

        sort($array);

        unset($array);
    }
}