<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AksesAPiController extends Controller
{
    public function apikey($key)
    {
        $apikey = env('API_KEY_BSA');
        $apikey = hash('sha256', $apikey);
        if ($key != "Bearer ". $apikey) {
            return false;
        } else {
            return true;
        }
    }
}
