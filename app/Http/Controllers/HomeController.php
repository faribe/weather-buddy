<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function welcome()
    {
        return response()->json([
            'service' => 'Weather Buddy API',
            'version' => '1.0',
        ]);
    }
}
