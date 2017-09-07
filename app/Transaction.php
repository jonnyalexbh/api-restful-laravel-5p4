<?php

namespace App;

use App\Buyer;
use App\Product;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  protected $fillable = [
    'quantity',
    'buyer_id',
    'product_id',
  ];
  
  // one-to-one relationship
  public function buyer()
  {
    return $this->belongsTo(Buyer::class);
  }
  // one-to-one relationship
  public function product()
  {
    return $this->belongsTo(Product::class);
  }

}
