<?php

namespace App\TaxDebt;

use Illuminate\Support\Facades\Log;

class TaxDebtResponse {

    /**
     * Tax debt parsed data
     * @access private
     * @var array
     */        
    private $data  = [];
    
    /**
     * Raw XML data converted to JSON
     * @access private
     * @var string
     */        
    private $json  = '';

    /**
     * Success data flag
     * @access private
     * @var bool
     */    
    private $success;

    /**
     * Parsed error value
     * @access private
     * @var string
     */        
    private $error;

    /**
     * Calls constructor. 
     * Takes XML and call extract function.
     * @access public
     * @param string $response XML from TaxDebt service 
     * @return void
     */            
    public function __construct($response)
    {
        $this->extract($this->toObject($response));
    }     

    /**
     * Fill extracting result values using XML converted to object
     * @access private
     * @param object $r XML converted to object
     * @return void
     */    
    private function extract($r)
    {
        $this->json = $this->toJSON($r);
        if(isset($r->responseCode)) {
            if($r->responseCode == 0) {
                $this->dataSet($r);
            }   
            if($r->responseCode != 0) {
                $this->errorSet($r->errormessageru);
            }
        } else {
            $this->errorSet('empty response code');
        }
    }

    /**
     * Convert XML to PHP object
     * @access private
     * @param string $response XML
     * @return object
     */    
    private function toObject($response)
    {
        return json_decode(json_encode(simplexml_load_string(str_replace("edw:", "", $response))));
    }

    /**
     * Convert PHP object to JSON
     * @access private
     * @param object $r PHP object
     * @return string
     */    
    private function toJSON($r)
    {
        if(!isset($r)) return '';

        // wrap in array if there is only one taxOrgInfo element
        if (isset($r->taxOrgInfo) && !is_array($r->taxOrgInfo)) { 
            $r->taxOrgInfo = [$r->taxOrgInfo]; 
        }

        // wrap in array if there is only one bccArrearsInfo element
        if (isset($r->taxOrgInfo)) {
            foreach($r->taxOrgInfo as $id=>$toInfo) {
                if (isset($r->taxOrgInfo[$id]->taxPayerInfo->bccArrearsInfo)
                    && !is_array($r->taxOrgInfo[$id]->taxPayerInfo->bccArrearsInfo)) {
                    $r->taxOrgInfo[$id]->taxPayerInfo->bccArrearsInfo = [$r->taxOrgInfo[$id]->taxPayerInfo->bccArrearsInfo];
                }
            }
        }

        
        return json_encode($r, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }

    /**
     * Setter for error value. 
     * Also sets success flag to 0.
     * Populates the data variable for bad variant.
     * @access private
     * @param string $error request error
     * @return void
     */
    private function errorSet($error)
    {
        $this->success = 0;
        $this->data = ["name" => 0, "curr_debt" => 0.00, "is_valid" => 0];
        $this->error = $error;
        Log::error('TaxDebtResponse ' . $error);
    }

    /**
     * Setter for data value. 
     * Also sets success flag to 1.
     * @access private
     * @param string $error request error
     * @return void
     */
    private function dataSet($r)
    {
        $this->success = 1;
        $this->data = ["name" => $r->nameRu, "curr_debt" => $r->totalTaxArrear+0, "is_valid" => 1];
    }

    /**
     * Getters
     */
    public function data()    { return $this->data;    }
    public function json()    { return $this->json;    }
    public function success() { return $this->success; }
    public function error()   { return $this->error;   }   

    /**
     * USAGE:
     *
     * <code>
     * $res = new TaxDebtResponse($xml);
     * print_r($res->data());
     * print $res->json();
     * if(!$res->success()) print $res->error();
     * </code>
     */

}
