<?php

namespace App\Http\Controllers\Iot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SmartDoorController extends Controller
{
    private string $cacheKey = 'smart_door_status';

    /**
     * Recebe eventos do ESP32 (Wokwi)
     */
    public function receiveEvent(Request $request)
    {
        $data = $request->validate([
            'door'       => 'required|string',   // OPEN / CLOSED
            'intrusion'  => 'required|boolean',
            'openCount'  => 'required|integer',
            'nightMode'  => 'required|boolean',
        ]);

        $data['updated_at'] = now()->toDateTimeString();

        Cache::put($this->cacheKey, $data);

        return response()->json([
            'status' => 'ok'
        ]);
    }

    /**
     * Retorna o estado atual (para o dashboard)
     */
    public function getStatus()
    {
        return response()->json(
            Cache::get($this->cacheKey, [
                'door'       => 'UNKNOWN',
                'intrusion'  => false,
                'openCount'  => 0,
                'nightMode'  => false,
                'updated_at' => null,
            ])
        );
    }

    /**
     * View do dashboard
     */
    public function dashboard()
    {
        return view('iot.smart-door.dashboard');
    }
}
