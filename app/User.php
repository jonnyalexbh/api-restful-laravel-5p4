<?php

namespace App;

use App\Transformers\UserTransformer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
  use Notifiable, SoftDeletes;

  const VERIFIED_USER = '1';
  const UNVERIFIED_USER = '0';

  const ADMIN_USER = 'true';
  const REGULAR_USER = 'false';

  public $transformer = UserTransformer::class;
  protected $table = 'users';
  protected $dates = ['deleted_at'];

  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'name',
    'email',
    'password',
    'verified',
    'verification_token',
    'admin',
  ];

  public function setNameAttribute($valor)
  {
    $this->attributes['name'] = strtolower($valor);
  }

  public function getNameAttribute($valor)
  {
    return ucwords($valor);
  }

  public function setEmailAttribute($valor)
  {
    $this->attributes['email'] = strtolower($valor);
  }

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [
    'password',
    'remember_token',
    'verification_token',
  ];

  public function isVerified()
  {
    return $this->verified == User::VERIFIED_USER;
  }

  public function isAdmin()
  {
    return $this->admin == User::ADMIN_USER;
  }

  public static function generateVerificationCode()
  {
    return str_random(40);
  }

}
