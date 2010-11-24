<?
include 'RestClient.class.php';
$twitter = RestClient::put("http://192.168.2.10/MDW/document_root/api/v1/ticket/2-128854097sd9-99167", '<?xml version="1.0" encoding="UTF-8"?>
<ticket>
    <apiKey>1234567890</apiKey>
    <name>Martin Venuš</name>
    <message>Zpráva</message>
</ticket>', null, null, "application/xml");
echo $twitter->getResponse();
//var_dump($twitter->getResponseCode());
//var_dump($twitter->getResponseMessage());
//var_dump($twitter->getResponseContentType());  
?>