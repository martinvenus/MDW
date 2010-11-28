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
 * Ticket managment
 *
 */
class Admin_HolidayPresenter extends Admin_BasePresenter {

    /** @var Form */
    protected $form;

    public function startup() {

        parent::startup();
    }

    /*
     * Výchozí akce presenteru
     */

    public function actionDefault() {
        
    }

    function actionShowZajezdy() {
        $response = RestModel::getZajezdyFromAPI();

        $odpoved = trim($response->getResponse());

        $xml = simplexml_load_string($odpoved);

        $orderedZajezdy = HolidayModel::getOrderedZajezdyByUserId($this->user->getIdentity()->id);

        $orderedZajezdy = $orderedZajezdy->fetchPairs('zajezdId', 'orderId');

        $this->template->orderedZajezdy = $orderedZajezdy;
        $this->template->zajezdy = $xml;
    }

    /**
     *
     * Pridani tiketu do seznamu uplatku (vyuziti API jineho tymu)
     * ID projektu ve vzdalenem systemu si ulozim do databaze
     *
     */
    public function actionOrderZajezd($id) {
        $data = '<Objednavka>
    <caId>MiDWa</caId>
    <cena>0</cena>
    <datumVytvoreni>2010-11-20T17:59:20.002Z</datumVytvoreni>
    <osoby>
      <jmeno>' . $this->user->getidentity()->name . '</jmeno>
      <prijmeni>' . $this->user->getidentity()->surname . '</prijmeni>
      <vek>0</vek>
    </osoby>
    <stav>prijata</stav>
    <uzivatelId>jardakiss@gmail.com</uzivatelId>
    <zajezdId>' . $id . '</zajezdId>
  </Objednavka>';

        $req = RestClientModel::post('http://fit-mdw-ws10-103-5.appspot.com/rest/Objednavky', $data, null, null, 'application/xml');

        print_r($req->getResponseCode());

        if ($req->getResponseCode() == 200) {

            $xmlDOM = new DOMDocument();
            $response = $req->getResponse();

            $response = trim($response);

            $xmlDOM->loadXML($response);

            $xml = simplexml_import_dom($xmlDOM);

            $objednavkaId = (String) $xml->objednavkaId;

            // TODO: Az opravi API dodelat vlozeni do DB ticketBribe


            echo $objednavkaId;

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

}