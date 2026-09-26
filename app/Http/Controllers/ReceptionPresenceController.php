<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ReceptionAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceptionPresenceController extends Controller
{
    public function heartbeat(Request $request, ReceptionAvailability $availability): JsonResponse
    {
        $user = $request->user();
        // Solo una sesión autenticada con el rol Recepcionista puede anunciar disponibilidad.
        abort_unless($user instanceof User && $user->hasRole('Recepcionista'), 403);

        $availability->heartbeat();

        return response()->json(['ok' => true]);
    }

    public function status(ReceptionAvailability $availability): JsonResponse
    {
        // La respuesta no se almacena en el navegador para que el cliente reciba el estado reciente.
        return response()
            ->json(['available' => $availability->isAvailable()])
            ->header('Cache-Control', 'no-store, private');
    }
}
