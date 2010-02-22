<?php

    require_once('newsoap.php');

    $server = new NewSoap('TestService');

    $server->getInterface()->addFunction(
        'helloWord', 
        'TestService',
        null,
        array(
            'name'=>NewSoap::XTYPE_STRING, 
        ), 
        array('result'=>NewSoap::XTYPE_OBJECT)
    );

    $server->startService();

    class TestService {

        public function helloWord($name)
        {
            return $name;
        }
    }
