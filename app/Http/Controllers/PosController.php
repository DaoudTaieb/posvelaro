<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $client = \Illuminate\Support\Facades\DB::table('clients')->where('nom', 'PASSAGER')->first();
        return view('caisse.pos', compact('client'));
    }
}
