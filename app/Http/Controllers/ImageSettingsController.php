<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador para gestión de imágenes del sistema
 *
 * INTEGRACIÓN CON ADMINLTE:
 * Este controlador permite gestionar las imágenes que sobreescriben
 * la configuración estática de AdminLTE en config/adminlte.php.
 *
 * Las imágenes subidas se almacenan en storage/app/public/images/
 * y son accesibles desde /storage/images/ para ser utilizadas
 * en las vistas personalizadas de AdminLTE.
 */
class ImageSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::current();
        return view('image_settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = Setting::current();
        $data = [];
        $nombres = [
            'logo' => 'logo.png',
            'login_logo' => 'login.png',
            'dashboard_logo' => 'dashboard.png',
            'spinner' => 'spinner.png',
            'favicon' => 'favicon.png',
        ];
        $carpetas = [
            'logo' => 'images/logo',
            'login_logo' => 'images/login',
            'dashboard_logo' => 'images/dashboard',
            'spinner' => 'images/spinner',
            'favicon' => 'favicon',
        ];
        foreach(['logo', 'login_logo', 'dashboard_logo', 'spinner', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                $folder = $carpetas[$field];
                $filename = $nombres[$field];
                $fullPath = $folder . '/' . $filename;
                // Eliminar archivo anterior si existe
                if (file_exists(public_path('storage/' . $fullPath))) {
                    unlink(public_path('storage/' . $fullPath));
                }
                // Guardar el nuevo archivo con el nombre fijo
                $request->file($field)->move(public_path('storage/' . $folder), $filename);
                $data[$field] = $fullPath;
            }
        }
        $settings->update($data);
        return redirect()->route('settings.images')->with('success', 'Imágenes actualizadas correctamente.');
    }
}
