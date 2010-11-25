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

    function actionGetTicket($ticketId) {

        $errors = array();

        $httpResponse = Environment::getHttpResponse();
        $httpResponse->setContentType('application/xml');


        $ticket = TicketsModel::getTicketDetailsByTicketID($ticketId);
        $ticketMessages = TicketsModel::getPublicTicketMessages($ticket['id']);

        if (count($ticket) == 0) {
            $httpResponse->setCode(404);
            array_push($errors, 'Ticket with given ID does not exist.');
        } else {
            $this->template->ticket = $ticket;
            $this->template->ticketMessages = $ticketMessages;
        }

        if (count($errors) > 0) {
            $this->template->errors = $errors;
        }
    }

    function actionCreateTicket() {

        $errors = array();
        $error = false;

        $httpResponse = Environment::getHttpResponse();
        $httpResponse->setContentType('application/xml');

        $verifyContentType = RestModel::verifyContentType('application/xml', Environment::getHttpRequest()->getHeader('Content-type'));

        if ($verifyContentType == false) {
            $httpResponse->setCode(400);
            array_push($errors, 'Content type must be application/xml.');
            $error = true;
        }

        $data = @file_get_contents('php://input');

        // Enable user error handling
        libxml_use_internal_errors(true);

        $xmlDOM = new DOMDocument();
        $xmlDOM->loadXML($data);
        //$xml->load($data);

        if (!$xmlDOM->schemaValidate(WWW_DIR . '/xsd/ticket.xsd')) {
            $httpResponse->setCode(400);
            array_push($errors, 'XML document is invalid.');
            $error = true;
        } else {
            //print 'XML document is valid.';

            $xml = simplexml_import_dom($xmlDOM);

            $pole = array();

            try {
                RestModel::verifyAPIkey((String) $xml->apiKey);
            } catch (AuthenticationException $e) {
                $httpResponse->setCode(403);
                array_push($errors, 'API Key is invalid.');
                $error = true;
            }


            if ($error == false) {

                // naplni se pole pro predani do modelu
                foreach ($xml as $key => $item) {
                    $pole[$key] = (String) $item;
                }

                $httpRquest = Environment::getHttpRequest();
                $pole['ip'] = $httpRquest->getRemoteAddress();

                $pole['departmentId'] = $pole['department'];
                $pole['name'] = $pole['author'];
                $pole['mobile'] = $pole['phone'];
                $pole['email'] = $pole['mail'];
                $pole['ticketMessage'] = $pole['description'];
                $pole['staffId'] = NULL;
                $pole['priority'] = 1;
                $pole['status'] = "Otevřený";
                $pole['source'] = "api";
                $pole['closed'] = 0;
                $pole['created'] = time();
                $pole['updated'] = time();
                $pole['time'] = time();
                $pole['type'] = 0;
                $pole['tid'] = Admin_TicketPresenter::genTicketID($pole['department']);

                try {
                    TicketsModel::addTicket($pole);
                    dibi::query('COMMIT');
                } catch (Exception $e) {
                    dibi::query('ROLLBACK');
                    Debug::processException($e);
                    $httpResponse->setCode(500);
                    array_push($errors, 'Server database error.');
                }
            }
        }

        if (count($errors) > 0) {
            $this->template->errors = $errors;
        } else {
            $this->template->ticket = $pole['tid'];
        }
    }

    function actionAddMessageTicket($ticketId) {
        $errors = array();
        $error = false;

        $httpResponse = Environment::getHttpResponse();
        $httpResponse->setContentType('application/xml');

        $verifyContentType = RestModel::verifyContentType('application/xml', Environment::getHttpRequest()->getHeader('Content-type'));

        if ($verifyContentType == false) {
            $httpResponse->setCode(400);
            array_push($errors, 'Content type must be application/xml.');
            $error = true;
        }

        $ticket = TicketsModel::getTicketDetailsByTicketID($ticketId);

        if (count($ticket) == 0) {
            $httpResponse->setCode(404);
            array_push($errors, 'Ticket with given ID does not exist.');
            $error = true;
        }

        $data = @file_get_contents('php://input');

        // Enable user error handling
        libxml_use_internal_errors(true);

        $xmlDOM = new DOMDocument();
        $xmlDOM->loadXML($data);
        //$xml->load($data);


        if (!$xmlDOM->schemaValidate(WWW_DIR . '/xsd/update.xsd')) {
            $httpResponse->setCode(400);
            array_push($errors, 'XML document is invalid.');
            $error = true;
        } else {
            //print 'XML document is valid.';

            $xml = simplexml_import_dom($xmlDOM);

            $pole = array();

            try {
                RestModel::verifyAPIkey((String) $xml->apiKey);
            } catch (AuthenticationException $e) {
                $httpResponse->setCode(403);
                array_push($errors, 'API Key is invalid.');
                $error = true;
            }

            if ($error == false) {

                // naplni se pole pro predani do modelu
                foreach ($xml as $key => $item) {
                    $pole[$key] = (String) $item;
                }

                $pole['tiket'] = $ticket['id'];
                $pole['message'] = $pole['message'];
                $pole['name'] = $pole['name'];
                $pole['time'] = time();
                $pole['type'] = 1;

                try {
                    TicketsModel::addReply($pole);
                    dibi::query('COMMIT');
                } catch (Exception $e) {
                    dibi::query('ROLLBACK');
                    Debug::processException($e);
                    $httpResponse->setCode(500);
                    array_push($errors, 'Server database error.');
                }
            }
        }


        if (count($errors) > 0) {
            $this->template->errors = $errors;
        }
    }

    function actionCloseTicket($ticketId) {
        $errors = array();
        $error = false;

        $httpResponse = Environment::getHttpResponse();
        $httpResponse->setContentType('application/xml');

        $verifyContentType = RestModel::verifyContentType('application/xml', Environment::getHttpRequest()->getHeader('Content-type'));

        if ($verifyContentType == false) {
            $httpResponse->setCode(400);
            array_push($errors, 'Content type must be application/xml.');
            $error = true;
        }

        $ticket = TicketsModel::getTicketDetailsByTicketID($ticketId);

        if (count($ticket) == 0) {
            $httpResponse->setCode(404);
            array_push($errors, 'Ticket with given ID does not exist.');
            $error = true;
        }

        $data = @file_get_contents('php://input');

        // Enable user error handling
        libxml_use_internal_errors(true);

        $xmlDOM = new DOMDocument();
        $xmlDOM->loadXML($data);

        if (!$xmlDOM->schemaValidate(WWW_DIR . '/xsd/close.xsd')) {
            $httpResponse->setCode(400);
            array_push($errors, 'XML document is invalid.');
            $error = true;
        } else {
            $xml = simplexml_import_dom($xmlDOM);

            try {
                RestModel::verifyAPIkey((String) $xml->apiKey);
            } catch (AuthenticationException $e) {
                $httpResponse->setCode(403);
                array_push($errors, 'API Key is invalid.');
                $error = true;
            }

            if ($error == false) {

                $pole['type'] = 3; // 3 = systémová zpráva

                $pole['tiket'] = $ticket['id'];

                $pole['comment'] = "Tiket byl uzavřen na přání zadavatele.";
                $pole['closed'] = 1;
                $pole['status'] = "Uzavřený";

                $pole['time'] = time();

                $pole['name'] = "System";

                try {
                    //TODO: Tady pošli data na uzavření tiketu
                    TicketsModel::changeClosed($pole);
                    dibi::query('COMMIT');
                } catch (Exception $e) {
                    dibi::query('ROLLBACK');
                    Debug::processException($e);
                    $httpResponse->setCode(500);
                    array_push($errors, 'Server database error.');
                }
            }
        }


        if (count($errors) > 0) {
            $this->template->errors = $errors;
        }
    }

}