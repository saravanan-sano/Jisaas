<?php

namespace App\SuperAdmin\Models;

use App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class UpiPayment extends Model
{
    protected $table = 'upipayments';

    protected $fillable = [
        'company_id',
        'customer_vpa',
        'amount',
        'client_txn_id',
        'customer_name',
        'customer_email',
        'customer_mobile',
        'p_info',
        'upi_txn_id',
        'status',
        'remark',
        'udf1',
        'udf2',
        'udf3',
        'redirect_url',
        'txnAt',
    ];

    // Define the relationship with the Company model
    public function company()
    {
        return $this->belongsTo(company::class);
    }
}
