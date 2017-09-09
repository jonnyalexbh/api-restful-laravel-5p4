<?php

namespace App;

use App\Seller;
use App\Category;
use App\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
  use SoftDeletes;

  const AVAILABLE_PRODUCT = 'available';
  const UNAVAILABLE_PRODUCT = 'unavailable';

  protected $dates = ['deleted_at'];
  protected $fillable = [
    'name',
    'description',
    'quantity',
    'status',
    'image',
    'seller_id',
  ];
  protected $hidden = [
    'pivot'
  ];

  public function isAvailable()
  {
    return $this->status == Product::AVAILABLE_PRODUCT;
  }

  // one-to-one relationship
  public function seller()
  {
    return $this->belongsTo(Seller::class);
  }
  // one-to-many relationship
  public function transactions()
  {
    return $this->hasMany(Transaction::class);
  }
  // many-to-many relationship
  public function categories()
  {
    return $this->belongsToMany(Category::class);
  }

}
