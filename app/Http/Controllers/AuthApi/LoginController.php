<?php

namespace App\Http\Controllers\AuthApi;

use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{

  private $client;

  public function __construct(){
    $this->client = Client::find(1);
  }

  public function login(Request $request){

    $params = [
      'grant_type' => 'password',
      'client_id' => $this->client->id,
      'client_secret' => $this->client->secret
    ];

    $request->request->add($params);

    $proxy = Request::create('oauth/token', 'POST');

    return Route::dispatch($proxy);

  }

}
