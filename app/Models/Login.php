<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Login extends Model{
    protected $table = 'usuarios';

    protected $fillable = ["nombre", "contrasenia"];

    public $timestamps = false;

}
