<?php

class NewSoapOperation
{
    private $input;
    private $outupt;
    private $name;
    private $class;
    private $namespace;

    public function __construct($name, $class, $namespace, array $input, array $output)
    {
        $this->name = $name;
        $this->input = $input;
        $this->output = $output;
        $this->class = $class;
        $this->namespace = $namespace;
    }

    public function getName() 
    { 
        return $this->name; 
    }

    public function getClassName() 
    { 
        return $this->class; 
    }

    public function toPortTypeOperation()
    {
        return "<operation name='".$this->name."'> <input message='".$this->name."Request' /><output message='".$this->name."Response' /> </operation>";
    }

    public function toBindingOperation()
    {
        $namespace = ($this->namespace) ? sprintf("namespace='%s'", $this->namespace) : '';

        return "<operation name='".$this->name."'>
                    <soap:operation soapAction=''/>
                        <input><soap:body use='encoded' ${namespace} encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'/></input>
                        <output><soap:body use='encoded' ${namespace} encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'/></output>
                </operation>";
    }

    public function toMessage()
    {
        if(empty($this->input)) return;

        $part = array();
        foreach($this->input as $name => $type)
        {
            $part[] = "<part name='".$name."' type='xsd:".$type."' />";
        }

        $message = "<message name='".$this->name."Request'> ".implode("\n", $part)." </message>";

        $part = array();
        foreach($this->output as $name => $type)
        {
            $part[] = "<part name='".$name."' type='xsd:".$type."' />";
        }

        $message .= "\n\n<message name='".$this->name."Response'> ".implode("\n", $part)." </message>";

        return $message;
    }
}
