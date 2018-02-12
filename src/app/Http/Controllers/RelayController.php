<?php

namespace App\Http\Controllers;

// Internals
use App\Relay;

// Externals
use Illuminate\Http\Request;

class RelayController extends Controller
{
    public function index(Request $request)
    {
        $query = Relay::query();
        // TODO: Filters
        
        $perPage = $request->input('per_page', 20);
        $relays = $query->paginate($perPage);
        return view('relays.index', [
            'relays' => $relays
        ]);
    }
    
    public function show(Request $request, Relay $relay)
    {
        return view('relays.show', [
            'relay' => $relay
        ]);
    }
}
