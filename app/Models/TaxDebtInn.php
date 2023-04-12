<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxDebtInn extends Model
{
    protected $table = 'tax_debt_inn';
    
    protected $primaryKey = 'id';

    public $timestamps = false;
    
    protected $fillable = [
    	'id',
        'inn',
    	'name',
    	'is_valid',
        'is_notifiable',
    	'prev_debt',
    	'curr_debt',
    	'notified_at',
    ];

    protected $attributes = [
        'name'             => 0,
        'is_valid'         => 0,
        'is_notifiable'    => 0,  
        'prev_debt'        => 0.00,
        'curr_debt'        => 0.00
    ];
    
}
