<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    public function linkCard(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
        ]);

        $user = $request->user();
        $uid  = strtoupper($request->uid);

        if ($user->rfid_uid) {
            return response()->json(['success' => false, 'message' => 'Card already linked'], 400);
        }

        if (User::where('rfid_uid', $uid)->exists()) {
            return response()->json(['success' => false, 'message' => 'Card already in use'], 400);
        }

        $user->update([
            'rfid_uid'      => $uid,
            'rfid_linked_at'=> now(),
            'status'        => 'active',
        ]);

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function unlinkCard(Request $request)
    {
        $user = $request->user();

        if (!$user->rfid_uid) {
            return response()->json(['success' => false, 'message' => 'No card linked'], 400);
        }

        $user->update([
            'rfid_uid'       => null,
            'rfid_linked_at' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Card unlinked']);
    }
}
