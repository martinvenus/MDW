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
     * Vytvoří tiket ve vzdáleném API
     * @action typ akce (0=vytvoření, 1=změna)
     * @param ticketId
     * @param subject předmět tiketu
     * @param description popis tiketu
     * @return návratová hodnota
     */

    public static function createTicketInRemoteAPI($ticketId, $subject, $description, $action = 0) {
        $data = '<project>
    <name>' . $ticketId . '</name>
    <type>service</type>
    <tags>' . $subject . '</tags>
    <description>' . $subject . ': ' . $description . '</description>
</project>';

        $url = 'http://fit-mdw-ws10-102-7.appspot.com/rest/projects/test?user=martin.venus@gmail.com';

        if ($action == 0) {
            $api = RestClientModel::post($url, $data, null, null, "application/xml");
        } elseif ($action == 1) {
            $api = RestClientModel::put($url, $data, null, null, "application/xml");
        } else {
            throw new Exception('Invalid type of action!');
        }

        return $api;
    }

    public static function getZajezdyFromApi() {
        $url = 'http://fit-mdw-ws10-103-5.appspot.com/rest/Zajezdy/xml';

        $response = RestClientModel::get($url);

        return $response;
    }

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

    /**
     *
     * Pridani tiketu do seznamu uplatku (vyuziti API jineho tymu)
     * ID projektu ve vzdalenem systemu si ulozim do databaze
     *
     */
    public static function addProject($detaily) {
        $data = '<project>
    <name>' . $detaily['tid'] . '</name>
    <type>service</type>
    <tags>RT System</tags>
    <description>' . $detaily['ticketMessage'] . '</description>
</project>';

        $req = RestClientModel::post('http://fit-mdw-ws10-102-7.appspot.com/rest/projects?user=ahJmaXQtbWR3LXdzMTAtMTAyLTdyCwsSBFVzZXIY4V0M', $data, null, null, 'application/xml');

        if ($req->getResponseCode() == 201) {

            $xmlDOM = new DOMDocument();
            $response = (String) $req->getResponse();
            $response = trim($response);
            $xmlDOM->loadXML($response);

            $xml = simplexml_import_dom($xmlDOM);

            $projectId = (String) $xml->id;

            try {
                TicketsModel::addBribe($detaily['ticketId'], $projectId);
                dibi::query('COMMIT');
            } catch (Exception $e) {
                dibi::query('ROLLBACK');
                Debug::processException($e);
            }
        }
    }

}

