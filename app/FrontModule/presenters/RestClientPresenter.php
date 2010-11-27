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

        $url = 'http://fit-mdw-ws10-102-7.appspot.com/rest/projects?user=martin.venus@gmail.com';
        $test = RestClientModel::post($url, $data, null, null, "application/xml");

        echo $test->getResponse();
        var_dump($test->getResponseCode());
//var_dump($test->getResponseMessage());
//var_dump($test->getResponseContentType());
    }

}
