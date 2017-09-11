<?php

namespace App\Http\Controllers\User;

use App\User;
use App\Mail\UserCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Transformers\UserTransformer;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{
  public function __construct()
  {
    $this->middleware('client.credentials')->only(['store', 'resend']);
    $this->middleware('transform.input:' . UserTransformer::class)->only(['store', 'update']);
  }
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    $usuarios = User::all();
    return $this->showAll($usuarios);
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
    $reglas = [
      'name' => 'required',
      'email' => 'required|email|unique:users',
      'password' => 'required|min:6|confirmed'
    ];

    $this->validate($request, $reglas);

    $campos = $request->all();
    $campos['password'] = bcrypt($request->password);
    $campos['verified'] = User::UNVERIFIED_USER;
    $campos['verification_token'] = User::generateVerificationCode();
    $campos['admin'] = User::REGULAR_USER;

    $usuario = User::create($campos);

    return $this->showOne($usuario, 201);
  }

  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show(User $user)
  {
    return $this->showOne($user);
  }

  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, User $user)
  {
    $rules = [
      'email' => 'email|unique:users,email,' . $user->id,
      'password' => 'min:6|confirmed',
      'admin' => 'in:' . User::ADMIN_USER . ',' . User::REGULAR_USER,
    ];

    if ($request->has('name')) {
      $user->name = $request->name;
    }

    if ($request->has('email') && $user->email != $request->email) {
      $user->verified = User::UNVERIFIED_USER;
      $user->verification_token = User::generateVerificationCode();
      $user->email = $request->email;
    }

    if ($request->has('password')) {
      $user->password = bcrypt($request->password);
    }

    if ($request->has('admin')) {
      if (!$user->isVerified()) {
        return $this->errorResponse('Only verified users can modify the admin field', 409);
      }
      $user->admin = $request->admin;
    }

    if (!$user->isDirty()) {
      return $this->errorResponse('You need to specify a different value to update', 422);
    }

    $user->save();

    return $this->showOne($user);

  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy(User $user)
  {
    $user->delete();
    return $this->showOne($user);
  }

  public function verify($token)
  {
    $user = User::where('verification_token', $token)->firstOrFail();

    $user->verified = User::VERIFIED_USER;
    $user->verification_token = null;

    $user->save();

    return $this->showMessage('The account has been verified succesfully');
  }

  public function resend(User $user)
  {
    if ($user->isVerified()) {
      return $this->errorResponse('This user is already verified', 409);
    }

    retry(5, function() use ($user) {
      Mail::to($user)->send(new UserCreated($user));
    }, 100);

    return $this->showMessage('The verification email has been resend');
  }

}
