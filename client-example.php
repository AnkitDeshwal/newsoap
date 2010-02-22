<?php
try
{
    $client = new SoapClient("http://localhost/newsoap/server-example.php?wsdl", array('trace'=> true, 'cache_wsdl' => 0, 'login'=>'demo', 'password'=>'demo'));

    $response = $client->helloWord('Demo');

    header('Content-type: text/plain');
    echo "Response:\t";
    var_dump($response);
    echo "Envelope:\t";
    var_dump($client->__getLastResponse());
}
catch(SoapFault $e)
{
    echo($e->getMessage());
}
