<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Http\Request;

class GocardlessController extends Controller
{
    public function callback(Request $request)
    {
        $ref = $request->query('ref');
        $integration = Integration::where('requisition_id', $ref)->first();
        abort_if(is_null($integration),404);
        if ($integration->can_accept)
        {
            $integration->fillExtra();
            $integration->save();
        }
        return response()->redirectTo(route('filament.admin.resources.transactions.index'));
    }
}
