<?php

namespace App;

use App\Product;

class Seller extends User
{
  // one-to-many relationship
  public function products()
  {
    return $this->hasMany(Product::class);
  }
}
