<?php

class NewSoapInterface
{
    private $operations = array();

    public function addFunction($method_name, $class = null, $namespace = null, array $input = array(), array $output = array())
    {
        if(in_array($class.$method_name, $this->operations)) 
        {
            throw new NewSoapException("Method $method_name already exist");
        }

        $Operation = new NewSoapOperation($method_name, $class, $namespace, $input, $output);

        $this->operations[$class.$method_name] = $Operation;
    }

    public function getFunctions()
    {
        return $this->operations;
    }

}
