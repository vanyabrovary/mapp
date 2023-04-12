<?php

namespace App\TaxDebt;

use App\TaxDebt\TaxDebtRequest;
use App\TaxDebt\TaxDebtResponse;

use App\Models\TaxDebtInn;
use App\Models\User;
use App\Events\TaxDebt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
        
class TaxDebtStore 
{
    /**
     * TaxDebtStore
     *
     * This class provides access to TaxDebt store 
     * 
     */

    /**
     * A debt request will be make for this INN
     * @access private
     * @var string
     */    
    private $inn;
    
    /**
     * This variable will be fill by model of this INN
     * @access private
     * @var string
     */    
    private $model;

    /**
     * Parsed data 
     * @access private
     * @var string
     */    
    private $data;

    /**
     * Data is changed
     * @access private
     * @var string
     */        
    private $is_changed;

    /**
     * Data is new
     * @access private
     * @var string
     */        
    private $is_new;
    
    /**
     * Success flag
     * @access private
     * @var bool
     */    
    private $success;

    /**
     * TaxDebt error
     * @access private
     * @var string
     */        
    private $error;

    /**
     * Calls constructor, fill INN value, make request, 
     * parse response and save parsed data to DB 
     * @access public
     * @param string $inn 
     */
    public function __construct($inn)
    {
	if(preg_match("/[0-9]{12}/", $inn)) {
    	    $this->inn = $inn;
    	    $this->makeRequestResponseSave();
	} else {
	    $this->errorSet('Bad INN format!');
	}
    }

    /**
     * Make request to TaxDEbt service
     * @access private
     * @return void
     */
    private function makeRequestResponseSave()
    {
        $obj = new TaxDebtRequest($this->inn);

        if($obj->success()) {
            $this->responseSet($obj->response());
            $this->extractResponse();
        }

        if(!$obj->success()) {
            $this->errorSet($obj->error());

            if ( preg_match("/429 Requests/", $obj->error()) ) {
                sleep(5);
            }

            //$obj->response($obj->error());
        }

    }

    /**
     * Parse response
     * @access private
     * @return void
     */         
    private function extractResponse()
    {         
        $obj = new TaxDebtResponse($this->response()); 
        
        if($obj->data()) {
            // set data as public
            $this->dataSet($obj);
            $this->save();    
        }        
        
        if(!$obj->success()) {
            $this->errorSet($obj->error());
        }
    }

    /**
     * Get player_ids for inn 
     * @access private
     * @return void
     */         
    public function playerId()
    {
        $rows = DB::table("users")->whereinn($this->inn)->get();
        $playerIds = [];
        foreach ($rows as $row) {
            if (isset($row->id)) {
                $model = User::find($row->id);
                $playerId = $model->playerId();
                if(isset($playerId)) array_push($playerIds, $playerId);
            }
        }
        if(count($playerIds) > 0) return $playerIds;
    }
   
    /**
     * Save parsed data to DB 
     * @access private
     * @return void
     */         
    private function save()
    {
        $row = DB::table("tax_debt_inn")->whereinn($this->inn)->first();

        if (isset($row->id)) {
        
            $model = TaxDebtInn::find($row->id);
            
            if($this->data["curr_debt"] != $model->curr_debt) $this->is_changed = 1;

        } else {
            
            $model = new TaxDebtInn;
            $this->is_new = 1;
        
        }
        
        if($this->is_changed == 1 || $this->is_new == 1 ) {
            $model->fill(array_merge(['inn' => $this->inn], $this->data()))->save();
        }

        // Notify new inn
        if($this->is_new == 1) {
            $playerIds = $this->playerId();
            if(isset($playerIds)) Event::dispatch(new TaxDebt($playerIds));
        }
        // set model as public
        $this->modelSet($model);
    }

    /**
     * EnableNotify
     * @access private
     * @return void
     */         
    public function enableNotify()
    {
        if (isset($this->model->id)) {
            $this->model->is_notifiable = "1";
            $this->model->save();
        }
    }

    /**
     * DisableNotify
     * @access private
     * @return void
     */         
    public function disableNotify()
    {
        if (isset($this->model->id)) {
            $this->model->is_notifiable = 0;
            $this->model->save();
        }
    }

    /**
     * Setter for response value. 
     * Also sets the success flag to 1.
     * @access private
     * @param string $error request error
     * @return void
     */
    private function responseSet($response)
    {
        $this->success = 1;
        $this->response = $response;
    }

    /**
     * Setter for data value. 
     * Also sets the success flag to 1.
     * @access private
     * @param string $error request error
     * @return void
     */
    private function dataSet($data)
    {
        $this->success = 1;
        $this->data = $data->data();
        $this->json = $data->json();
    }

    /**
     * Setter for model value. 
     * Also sets the success flag to 1.
     * @access private
     * @param string $error request error
     * @return void
     */
    private function modelSet($model)
    {
        $this->success = 1;
        $this->model = $model;
    }

    /**
     * Setter for error value. 
     * Also sets the success flag to 0.
     * @access private
     * @param string $error request error
     * @return void
     */
    private function errorSet($error)
    {
        $this->success = 0;
        $this->error = $error;
    }

    private function response() { return $this->response; }

    public  function model()    { return $this->model;    }
    public  function data()     { return $this->data;     }
    public  function json()     { return $this->json;     }

    public  function success()  { return $this->success;  }
    public  function error()    { return $this->error;    }
}
