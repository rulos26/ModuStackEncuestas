<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Encuesta;
use App\Models\Respuesta;

class Pregunta extends Model
{
    protected $fillable = [
        'encuesta_id',
        'texto',
        'descripcion',
        'placeholder',
        'tipo',
        'orden',
        'obligatoria',
        'min_caracteres',
        'max_caracteres',
        'escala_min',
        'escala_max',
        'escala_etiqueta_min',
        'escala_etiqueta_max',
        'tipos_archivo_permitidos',
        'tamano_max_archivo',
        'condiciones_mostrar',
        'logica_salto',
        'opciones_filas',
        'opciones_columnas',
        'latitud_default',
        'longitud_default',
        'zoom_default'
    ];

    protected $casts = [
        'obligatoria' => 'boolean',
        'condiciones_mostrar' => 'array',
        'logica_salto' => 'array',
        'opciones_filas' => 'array',
        'opciones_columnas' => 'array',
        'escala_min' => 'integer',
        'escala_max' => 'integer',
        'zoom_default' => 'integer',
        'latitud_default' => 'decimal:8',
        'longitud_default' => 'decimal:8'
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class);
    }

    public function respuestas()
    {
        return $this->hasMany(Respuesta::class);
    }

    /**
     * Obtener los tipos de preguntas disponibles
     */
    public static function getTiposDisponibles()
    {
        return [
            'respuesta_corta' => [
                'nombre' => 'Respuesta corta (texto)',
                'descripcion' => 'Texto breve (nombre, correo, cargo, etc.)',
                'icono' => 'fas fa-font',
                'necesita_respuestas' => false,
                'necesita_opciones' => false
            ],
            'parrafo' => [
                'nombre' => 'Párrafo (texto largo)',
                'descripcion' => 'Respuesta de texto largo o desarrollo',
                'icono' => 'fas fa-align-left',
                'necesita_respuestas' => false,
                'necesita_opciones' => false
            ],
            'seleccion_unica' => [
                'nombre' => 'Selección única (radio)',
                'descripcion' => 'Escoger solo una opción entre varias',
                'icono' => 'fas fa-dot-circle',
                'necesita_respuestas' => true,
                'necesita_opciones' => true
            ],
            'casillas_verificacion' => [
                'nombre' => 'Casillas de verificación (checkbox)',
                'descripcion' => 'Elegir una o varias opciones',
                'icono' => 'fas fa-check-square',
                'necesita_respuestas' => true,
                'necesita_opciones' => true
            ],
            'lista_desplegable' => [
                'nombre' => 'Lista desplegable (dropdown)',
                'descripcion' => 'Seleccionar una opción desde un menú desplegable',
                'icono' => 'fas fa-chevron-down',
                'necesita_respuestas' => true,
                'necesita_opciones' => true
            ],
            'escala_lineal' => [
                'nombre' => 'Escala lineal (0 a 10, etc.)',
                'descripcion' => 'Calificación en rango numérico',
                'icono' => 'fas fa-sliders-h',
                'necesita_respuestas' => false,
                'necesita_opciones' => false
            ],
            'cuadricula_opcion_multiple' => [
                'nombre' => 'Cuadrícula de opción múltiple',
                'descripcion' => 'Tabla con varias filas y columnas de opciones',
                'icono' => 'fas fa-table',
                'necesita_respuestas' => true,
                'necesita_opciones' => true
            ],
            'cuadricula_casillas' => [
                'nombre' => 'Cuadrícula de casillas',
                'descripcion' => 'Tabla donde puedes marcar múltiples opciones en cada fila',
                'icono' => 'fas fa-th',
                'necesita_respuestas' => true,
                'necesita_opciones' => true
            ],
            'fecha' => [
                'nombre' => 'Fecha',
                'descripcion' => 'Selector de fecha',
                'icono' => 'fas fa-calendar',
                'necesita_respuestas' => false,
                'necesita_opciones' => false
            ],
            'hora' => [
                'nombre' => 'Hora',
                'descripcion' => 'Selector de hora',
                'icono' => 'fas fa-clock',
                'necesita_respuestas' => false,
                'necesita_opciones' => false
            ],
            'carga_archivos' => [
                'nombre' => 'Carga de archivos',
                'descripcion' => 'Permitir al usuario subir un archivo',
                'icono' => 'fas fa-upload',
                'necesita_respuestas' => false,
                'necesita_opciones' => false
            ],
            'ubicacion_mapa' => [
                'nombre' => 'Ubicación / mapa',
                'descripcion' => 'Selección geográfica',
                'icono' => 'fas fa-map-marker-alt',
                'necesita_respuestas' => false,
                'necesita_opciones' => false
            ],
            'logica_condicional' => [
                'nombre' => 'Lógica condicional / salto',
                'descripcion' => 'Mostrar preguntas según respuestas anteriores',
                'icono' => 'fas fa-code-branch',
                'necesita_respuestas' => false,
                'necesita_opciones' => false
            ]
        ];
    }

    /**
     * Verificar si el tipo de pregunta necesita respuestas predefinidas
     */
    public function necesitaRespuestas()
    {
        $tipos = self::getTiposDisponibles();
        return $tipos[$this->tipo]['necesita_respuestas'] ?? false;
    }

    /**
     * Verificar si el tipo de pregunta necesita opciones configuradas
     */
    public function necesitaOpciones()
    {
        $tipos = self::getTiposDisponibles();
        return $tipos[$this->tipo]['necesita_opciones'] ?? false;
    }

    /**
     * Obtener la configuración del tipo de pregunta
     */
    public function getConfiguracionTipo()
    {
        $tipos = self::getTiposDisponibles();
        return $tipos[$this->tipo] ?? null;
    }

    /**
     * Obtener el nombre legible del tipo
     */
    public function getNombreTipo()
    {
        $config = $this->getConfiguracionTipo();
        return $config['nombre'] ?? $this->tipo;
    }

    /**
     * Obtener el icono del tipo
     */
    public function getIconoTipo()
    {
        $config = $this->getConfiguracionTipo();
        return $config['icono'] ?? 'fas fa-question';
    }

    /**
     * Scope para preguntas que necesitan respuestas
     */
    public function scopeNecesitaRespuestas($query)
    {
        return $query->whereIn('tipo', [
            'seleccion_unica',
            'casillas_verificacion',
            'lista_desplegable',
            'cuadricula_opcion_multiple',
            'cuadricula_casillas'
        ]);
    }

    /**
     * Scope para preguntas de texto
     */
    public function scopeEsTexto($query)
    {
        return $query->whereIn('tipo', ['respuesta_corta', 'parrafo']);
    }

    /**
     * Scope para preguntas de fecha/hora
     */
    public function scopeEsFechaHora($query)
    {
        return $query->whereIn('tipo', ['fecha', 'hora']);
    }

    /**
     * Scope para preguntas de archivo
     */
    public function scopeEsArchivo($query)
    {
        return $query->where('tipo', 'carga_archivos');
    }

    /**
     * Scope para preguntas de ubicación
     */
    public function scopeEsUbicacion($query)
    {
        return $query->where('tipo', 'ubicacion_mapa');
    }

    /**
     * Scope para preguntas de escala
     */
    public function scopeEsEscala($query)
    {
        return $query->where('tipo', 'escala_lineal');
    }
}
