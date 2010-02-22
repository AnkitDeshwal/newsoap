<?php
require_once('operation.php');
require_once('interface.php');

class NewSoapServer extends SoapServer
{
    private $debug = array();

    public function handle($request = null)
    {
        $debugIndex = sizeof($this->debug);

        // check variable HTTP_RAW_POST_DATA
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) 
        {
            $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents('php://input');
        }
                        
        // check input param
        if (is_null($request)) 
        {
            $request = $GLOBALS['HTTP_RAW_POST_DATA']; 
        }

        if(!$request) 
        {
            $this->debug[$debugIndex]['REQUEST'] = 'REQUEST WAS EMPTY';
            header('Content-type: text/html');
            die('Server Html Description not implemented');
        }

        $this->debug[$debugIndex]['REQUEST'] = $request;

        $result = parent::handle($request);

        $this->debug[$debugIndex]['RESULT'] = gettype($result);

        return $result;
    }

    public function getDebug()
    {
        return $this->debug;
    }
}

class NewSoap 
{
    const XTYPE_STRING        = 'string';
    const XTYPE_ARRAY         = 'Array';
    const XTYPE_OBJECT        = 'object';

    private $server         = null;
    private $name           = null;
    private $Interface;
    private $ws_uri         = null;
    private $wsdl_uri       = null;

    public function __construct($name, $ws_uri = null, $wsdl_uri = null)
    {
        $protocol = (strpos($_SERVER['SERVER_PROTOCOL'],'HTTPS') === false) ? 'http' : 'https';
        $uri = $protocol.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

        $this->name = $name;

        $this->ws_uri = ($ws_uri) ? $ws_uri : str_ireplace(array('?wsdl','&wsdl'), '', $uri);

        $this->wsdl_uri = ($wsdl_uri) ? $wsdl_uri : (strpos($uri, '?') === false) ? $this->ws_uri.'?wsdl' : $this->ws_uri.'&wsdl';

        $this->Interface = new NewSoapInterface();
    }

    static public function error($msg)
    {
        throw  new SoapFault('error', $msg); 
    }

    public function getDebug()
    {
        if($this->server)
        {
            return $this->server->getDebug();
        }

    }

    public function startService()
    {
        if(array_key_exists('wsdl', $_GET) || in_array('wsdl', $_GET)) 
        {
            echo $this->createWsdl();
            exit;
        }

        $this->createServer();
    }

    private function createServer()
    {
        $options = array(
                'cache_wsdl' => WSDL_CACHE_NONE, 
                );

        $this->server = new NewSoapServer($this->wsdl_uri, $options);

        foreach($this->Interface->getFunctions() as $method)
        {
            if($method->getClassName())
            {
                $this->server->setClass($method->getClassName());
            }
            else
            {
                $this->server->addFunction($method->getName());
            }
        }

        $this->server->handle();
    }

    public function getInterface()
    {
        return $this->Interface;
    }

    public function createWsdl()
    {

        $message = array();
        $portTypeOp = array();
        $bindingOp = array();

        $operations = $this->Interface->getFunctions();

        foreach($operations as $operation)
        {
            $message[]      = $operation->toMessage();
            $portTypeOp[]   = $operation->toPortTypeOperation();
            $bindingOp[]    = $operation->toBindingOperation();
        }

        header('Content-type: text/xml');
        $wsdl = "<?xml version='1.0' encoding='UTF-8' ?>
                    <definitions name='".$this->name."' targetNamespace='".$this->name."'
                        xmlns:soap='http://schemas.xmlsoap.org/wsdl/soap/' 
                        xmlns:xsd='http://www.w3.org/2001/XMLSchema' 
                        xmlns:soapenc='http://schemas.xmlsoap.org/soap/encoding/' 
                        xmlns:wsdl='http://schemas.xmlsoap.org/wsdl/' 
                        xmlns='http://schemas.xmlsoap.org/wsdl/'>
                    ";

        $wsdl .= implode("\n", $message);
        $wsdl .= "<portType name='".$this->name."PortType'>";
        $wsdl .= implode("\n", $portTypeOp);
        $wsdl .= "</portType>";
        $wsdl .= "<binding name='".$this->name."Binding' type='".$this->name."PortType'>";
        $wsdl .= "<soap:binding style='rpc' transport='http://schemas.xmlsoap.org/soap/http'/>";
        $wsdl .= implode("\n", $bindingOp);
        $wsdl .= "</binding>";
        $wsdl .= "<service name='".$this->name."ServerService'>";
        $wsdl .= "<port name='".$this->name."ServerPort' binding='".$this->name."Binding'><soap:address location='".str_replace('&', '&amp;', $this->ws_uri)."' /></port>";
        $wsdl .= "</service>";
        $wsdl .= "</definitions>"; 
        
        return $wsdl;
    }
}

class NewSoapException extends Exception {}
