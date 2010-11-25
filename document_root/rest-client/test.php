<?
include 'RestClient.class.php';

$twitter = RestClient::put("http://192.168.2.10/MDW/document_root/api/v1/ticket/2-1288540979-99167", '<?xml version="1.0" encoding="UTF-8"?>
<ticket>
    <apiKey>1234567890</apiKey>
</ticket>', null, null, "application/xml");
 
//$twitter = RestClient::get("http://mdw.wsolution.cz/api/v1/ticket/2-1288540979-99167", array('id'=>2-1288540979-99167));
echo $twitter->getResponse();
//var_dump($twitter->getResponseCode());
//var_dump($twitter->getResponseMessage());
//var_dump($twitter->getResponseContentType());  
?>