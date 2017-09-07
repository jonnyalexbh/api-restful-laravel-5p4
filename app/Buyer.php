<?php

namespace App;

use App\Transaction;

class Buyer extends User
{
  // one-to-many relationship
  public function transactions()
  {
    return $this->hasMany(Transaction::class);
  }
}
