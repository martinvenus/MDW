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
 * API presenter
 *
 */
class Front_RestClientPresenter extends Front_BasePresenter {

    function actionDefault() {
        $data = '<project>
    <name>Zda_se_ze_ani_projekt_s_otaznikem_NEFUNGUJE</name>
    <type>service</type>
    <tags>NEFUNGUJE</tags>
    <description>kdde se to zobrazuje?</description>
</project>';

        $bribe = '<bribe>
    <state>accepted</state>
    <oficialNote>{acception/rejection clarification}</oficialNote>
</bribe>';

        $url = 'http://fit-mdw-ws10-102-7.appspot.com/rest/projects/test?user=martin.venus@gmail.com';
        //$test = RestClientModel::post($url, $data, null, null, "application/xml");
        //$test = RestClientModel::delete('http://fit-mdw-ws10-102-7.appspot.com/rest/projects/martin+-+test');

        $test = RestClientModel::put('http://fit-mdw-ws10-102-7.appspot.com/rest/bribes/ahJmaXQtbWR3LXdzMTAtMTAyLTdyQwsSB1Byb2plY3QiK3pkYV9zZV96ZV9hbmlfcHJvamVrdF9zX290YXpuaWtlbV9uZWZ1bmd1amUMCxIFQnJpYmUYAQw', $bribe);
        echo $test->getResponse();
        var_dump($test->getResponseCode());
//var_dump($test->getResponseMessage());
//var_dump($test->getResponseContentType());
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

        $req = RestClientModel::post('http://fit-mdw-ws10-102-7.appspot.com/rest/projects?user=jardakiss@gmail.com', $data, null, null, 'application/xml');

        if ($req->getResponseCode() == 201) {

            $xmlDOM = new DOMDocument();
            $response = (String) $req->getResponse();
            $response = trim($response);
            $xmlDOM->loadXML($response);

            $xml = simplexml_import_dom($xmlDOM);

            $projectId = (String) $xml->id;

            // TODO: Az opravi API dodelat vlozeni do DB ticketBribe

            echo $projectId;



//
//            try {
//                TicketsModel::addBribe($detaily['ticketId'], $projectId);
//                dibi::query('COMMIT');
//            } catch (Exception $e) {
//                dibi::query('ROLLBACK');
//                Debug::processException($e);
//            }
        }
    }

    function actionTest() {
        $data = '<?xml version="1.0" encoding="UTF-8" ?>
<project>
    <id>3w</id>
</project>';

        $xmlDOM = new DOMDocument();
        $xmlDOM->loadXML($data);
        $xml = simplexml_import_dom($xmlDOM);
        $bribeId = (String) $xml->id;
    }

}
