<?php

namespace Modules\Software\Classes;


class SMSFactory
{
    private $gateway;
    public function __construct($gateway = null)
    {
        if ($gateway == 'JAWALY') {
            $this->gateway = new JAWALY();
        }else if ($gateway == 'SALA') {
            $this->gateway = new SALA();
        } else {
            $this->gateway = new SMSEG();
        }
        return $this->gateway;
    }

    public function getBalance(){
        return $this->gateway->getBalance();
    }
    public function send($phoneNumber, $msg){
        return $this->gateway->send($phoneNumber, $msg);
    }


}
