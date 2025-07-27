<?php


namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Support\Facades\Auth;
use Exception;

class EncuestaPreviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function preview($id)
    {
        try {
            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa', 'user'])->findOrFail($id);

            // Verificar permisos - solo el propietario puede ver
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para ver esta encuesta.');
            }

            return view('encuestas.preview', compact('encuesta'));
        } catch (Exception $e) {
            return redirect()->route('encuestas.index')->with('error', 'Encuesta no encontrada.');
        }
    }
}
