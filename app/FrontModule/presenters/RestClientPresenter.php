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
        $data = '<?xml version="1.0" encoding="UTF-8">
<project>
    <name>TEST</project>
    <type>TEST</type>
    <tags>TEST</tags>
    <description>TEST</description>
</project>';

        $url = 'http://fit-mdw-ws10-102-7.appspot.com/rest/projects';
        $test = RestClientModel::post($url,$data,null,null,"application/xml");

        //$test = RestClientModel::put("http://mdw.wsolution.cz/api/v1/ticket/2-1288540979-99167", $data, null, null, "application/xml");

//$twitter = RestClient::get("http://mdw.wsolution.cz/api/v1/ticket/2-1288540979-99167", array('id'=>2-1288540979-99167));
        echo $test->getResponse();
//var_dump($twitter->getResponseCode());
//var_dump($twitter->getResponseMessage());
//var_dump($twitter->getResponseContentType());
    }

}
