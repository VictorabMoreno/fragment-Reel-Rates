<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function getJson()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'phone' => '1234567890'
        ];
        
        return response()->json($data);
    }
}
