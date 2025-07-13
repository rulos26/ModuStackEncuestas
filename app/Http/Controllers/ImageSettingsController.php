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
        foreach(['logo', 'login_logo', 'dashboard_logo', 'spinner', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                $folder = match($field) {
                    'logo' => 'images/logo',
                    'login_logo' => 'images/login',
                    'dashboard_logo' => 'images/dashboard',
                    'spinner' => 'images/spinner',
                    'favicon' => 'images/favicon',
                    default => 'images',
                };
                $path = $request->file($field)->store($folder, 'public');
                $data[$field] = $path;
            }
        }
        $settings->update($data);
        return redirect()->route('settings.images')->with('success', 'Imágenes actualizadas correctamente.');
    }
}
