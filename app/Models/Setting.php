<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para configuración de imágenes del sistema
 *
 * INTEGRACIÓN CON ADMINLTE:
 * Este modelo permite sobreescribir dinámicamente las imágenes de AdminLTE
 * definidas en config/adminlte.php. Las vistas personalizadas en
 * resources/views/vendor/adminlte/ priorizan los valores de la base de datos
 * sobre los valores de configuración estática.
 *
 * Campos disponibles:
 * - logo: Logo principal (usado en sidebar, navbar y favicon)
 * - login_logo: Logo específico para páginas de autenticación
 * - dashboard_logo: Logo específico para el dashboard
 * - spinner: Imagen del preloader/spinner
 * - favicon: Favicon personalizado del sistema
 *
 * Las imágenes se almacenan en storage/app/public/images/ y son accesibles
 * desde /storage/images/ gracias al enlace simbólico de Laravel.
 */
class Setting extends Model
{
    protected $fillable = [
        'logo', 'login_logo', 'dashboard_logo', 'spinner', 'favicon'
    ];

    /**
     * Obtiene la configuración actual o crea una nueva si no existe
     *
     * @return Setting
     */
    public static function current()
    {
        return self::first() ?? self::create([]);
    }

    // Agrega métodos para obtener la URL pública de cada imagen
    public function getLogoUrl()
    {
        return $this->logo ? '/public/storage/logo/' . basename($this->logo) : null;
    }
    public function getLoginLogoUrl()
    {
        return $this->login_logo ? '/public/storage/login/' . basename($this->login_logo) : null;
    }
    public function getDashboardLogoUrl()
    {
        return $this->dashboard_logo ? '/public/storage/dashboard/' . basename($this->dashboard_logo) : null;
    }
    public function getSpinnerUrl()
    {
        return $this->spinner ? '/public/storage/spinner/' . basename($this->spinner) : null;
    }
    public function getFaviconUrl()
    {
        return $this->favicon ? '/public/storage/favicon/' . basename($this->favicon) : null;
    }
}
