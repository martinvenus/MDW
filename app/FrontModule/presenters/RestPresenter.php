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
class Front_RestPresenter extends Front_BasePresenter {

    function actionGetTest($action) {
        echo $action;
    }

    function actionGetTicket($ticketId) {

        $errors = array();

        $httpResponse = Environment::getHttpResponse();
        $httpResponse->setContentType('application/xml');


        $ticket = TicketsModel::getTicketDetails($ticketId);
        $ticketMessages = TicketsModel::getPublicTicketMessages($ticketId);

        if (count($ticket) == 0){
            $httpResponse->setCode(404);
            array_push($errors, 'Ticket with given ID does not exists.');
        }
        else {
            $this->template->ticket = $ticket;
            $this->template->ticketMessages = $ticketMessages;
        }

        if (count($errors) > 0) {
            $this->template->errors = $errors;
        }
    }

    function actionCreateTicket() {

        $errors = array();

        $httpResponse = Environment::getHttpResponse();
        $httpResponse->setContentType('application/xml');

        //TODO: Zavolat funkci na ověření typu obsahu

        $data = @file_get_contents('php://input');

        // Enable user error handling
        libxml_use_internal_errors(true);

        $xmlDOM = new DOMDocument();
        $xmlDOM->loadXML($data);
        //$xml->load($data);

        if (!$xmlDOM->schemaValidate(WWW_DIR . '/xsd/ticket.xsd')) {
            $httpResponse->setCode(400);
            array_push($errors, 'XML document is invalid.');
        } else {
            //print 'XML document is valid.';

            $xml = simplexml_import_dom($xmlDOM);

            //TODO: Zavolat funkci na ověření API key
            if ($xml->apiKey == 1234567890) {
                foreach ($xml as $key => $item) {
                    //echo $key . ' - ' . $item;
                    //echo "<br /><br />";
                }
            } else {
                $httpResponse->setCode(403);
                array_push($errors, 'API Key is invalid.');
            }
        }

        if (count($errors) > 0) {
            $this->template->errors = $errors;
        }
    }

}