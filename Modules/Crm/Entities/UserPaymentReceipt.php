<?php
namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserPaymentReceipt
 * @package App\Models
 */
class UserPaymentReceipt extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $table = 'CG_USER_PAYMENT_RECEIPTS';
    protected $fillable = ['UPR_ID', 'UPR_USER_ID','UPR_AMOUNT','UPR_PAYMENT_MODE','UPR_PAYMENT_REF_NUMBER','UPR_CREATED_BY','UPR_CREATED_DATE'];
    public $timestamps = false;
    protected $primaryKey = 'UPR_ID';

   

    public function user() {
        return $this->hasOne('Modules\crm\Entities\User', 'USER_ID', 'UPR_USER_ID');
    }

    // public function paymentmethod() {
    //     return $this->hasOne('Modules\Account\Entities\PaymentMethod', 'PAYM_ID', 'REC_PAYMENT_MODE');//->where('NN_PAYMENT_METHOD.PAYM_TENANT_ID','NN_RECEIPT.REC_TENANT_ID');
    // }
}
