<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    public function linkCard(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    public function unlinkCard(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }
}
