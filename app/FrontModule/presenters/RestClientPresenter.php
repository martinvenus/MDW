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

        $bribe='<bribe>
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

}
