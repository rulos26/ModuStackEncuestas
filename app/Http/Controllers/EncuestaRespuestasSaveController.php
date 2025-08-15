<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EncuestaRespuestasSaveController extends Controller
{
    //
    public function save(Request $request, $id)
    {
        dd($request->all(),$id);
    }
}
