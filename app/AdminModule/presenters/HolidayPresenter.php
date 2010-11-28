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
     * Objednani zajezdu pro zamestnance (vyuziti API jineho tymu)
     *
     */
    public function actionOrderZajezd($zaId, $caId, $cena) {
        $data = '<Objednavka>
    <caId>' . $caId . '</caId>
    <cena>' . $cena . '</cena>
    <datumVytvoreni>' . date("Y-m-d\TH:i:s.u") . '</datumVytvoreni>
    <osoby>
      <jmeno>' . $this->user->getidentity()->name . '</jmeno>
      <prijmeni>' . $this->user->getidentity()->surname . '</prijmeni>
      <vek>0</vek>
    </osoby>
    <stav>prijata</stav>
    <uzivatelId>jardakiss@gmail.com</uzivatelId>
    <zajezdId>' . $zaId . '</zajezdId>
  </Objednavka>';

        $req = RestClientModel::post('http://fit-mdw-ws10-103-5.appspot.com/rest/Objednavky', $data, null, null, 'application/xml');

        if ($req->getResponseCode() == 200) {

            $xmlDOM = new DOMDocument();
            $response = $req->getResponse();

            $response = trim($response);

            $xmlDOM->loadXML($response);

            $xml = simplexml_import_dom($xmlDOM);

            $objednavkaId = (String) $xml->objednavkaId;

            try {
                HolidayModel::addBonus($this->user->getidentity()->id, $zaId, $objednavkaId);
                dibi::query('COMMIT');
                $this->flashMessage("Zájezd byl úspěšně objednán");
            } catch (Exception $e) {
                dibi::query('ROLLBACK');
                Debug::processException($e);
                $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
            }
        } else {
            $this->flashMessage("Zájezd se nepodařilo rezervovat.");
        }

        $this->redirect('Holiday:showZajezdy');
    }

    /**
     *
     * Zruseni objednavky zamestnance (vyuziti API jineho tymu)
     *
     */
    public function actionCancelZajezd($objId) {

        $url = "http://fit-mdw-ws10-103-5.appspot.com/rest/Objednavky/cancel/" . $objId;

        $req = RestClientModel::put($url, null, null, null, 'text/html');

        if ($req->getResponseCode() == 204) {

            try {
                HolidayModel::removeBonus($objId);
                dibi::query('COMMIT');
                $this->flashMessage("Zájezd byl úspěšně zrušen.");
            } catch (Exception $e) {
                dibi::query('ROLLBACK');
                Debug::processException($e);
                $this->flashMessage(ERROR_MESSAGE . " Error description: " . $e->getMessage(), 'error');
            }
        } else {
            $this->flashMessage("Zájezd se nepodařilo zrušit.");
        }

        $this->redirect('Holiday:showZajezdy');
    }

}