# Mejoras en el Wizard de Preguntas

## Problema Resuelto

El wizard de preguntas perdía el estado de la encuesta seleccionada cuando el usuario intentaba agregar otra pregunta después de crear una. Esto ocurría porque el sistema no mantenía correctamente el ID de la encuesta entre las diferentes etapas del wizard.

## Soluciones Implementadas

### 1. Gestión Mejorada de Sesiones

**Archivo modificado:** `app/Http/Controllers/PreguntaWizardController.php`

- **Método `create()`**: Ahora verifica si existe una encuesta en la sesión antes de requerir una nueva selección
- **Método `confirm()`**: Mantiene la sesión activa cuando el usuario quiere continuar agregando preguntas
- **Método `cancel()`**: Limpia tanto la sesión como las cookies del wizard

### 2. Middleware de Gestión de Estado

**Archivo creado:** `app/Http/Middleware/WizardSessionMiddleware.php`

Este middleware proporciona:
- **Respaldo con cookies**: Si la sesión se pierde, recupera el estado desde las cookies
- **Sincronización automática**: Mantiene sincronizadas las sesiones y cookies
- **Persistencia mejorada**: El estado del wizard se mantiene incluso si hay problemas con las sesiones

### 3. Interfaz de Usuario Mejorada

**Archivos modificados:**
- `resources/views/preguntas/wizard/index.blade.php`
- `resources/views/preguntas/wizard/confirm.blade.php`

**Mejoras en la interfaz:**
- **Indicador de sesión activa**: Muestra claramente cuando hay una sesión de wizard en progreso
- **Botón de continuar**: Permite al usuario continuar agregando preguntas sin perder el estado
- **Información detallada**: Muestra el ID de la encuesta y el progreso actual
- **Estadísticas mejoradas**: Cuatro tarjetas informativas en lugar de tres

### 4. Comando de Limpieza

**Archivo creado:** `app/Console/Commands/CleanExpiredWizardSessions.php`

Este comando:
- Limpia sesiones de wizard expiradas (más de 2 horas)
- Se puede ejecutar manualmente: `php artisan wizard:clean-sessions`
- Registra la actividad en los logs

## Configuración

### Middleware Registrado

El middleware `WizardSessionMiddleware` está registrado en `bootstrap/app.php`:

```php
'wizard.session' => \App\Http\Middleware\WizardSessionMiddleware::class,
```

### Rutas Protegidas

Todas las rutas del wizard ahora usan el middleware:

```php
Route::middleware(['auth', 'wizard.session'])->group(function () {
    // Rutas del wizard...
});
```

## Flujo de Funcionamiento

1. **Selección inicial**: El usuario selecciona una encuesta
2. **Guardado en sesión**: El ID de la encuesta se guarda en sesión y cookies
3. **Creación de preguntas**: El usuario puede crear múltiples preguntas
4. **Continuación**: Al hacer clic en "Agregar Otra Pregunta", el sistema mantiene el estado
5. **Finalización**: Al finalizar, se limpian tanto sesiones como cookies

## Beneficios

- ✅ **Persistencia del estado**: El wizard mantiene la encuesta seleccionada
- ✅ **Experiencia fluida**: No hay interrupciones al agregar múltiples preguntas
- ✅ **Respaldo robusto**: Cookies como respaldo de las sesiones
- ✅ **Limpieza automática**: Sistema para limpiar sesiones expiradas
- ✅ **Interfaz clara**: Indicadores visuales del estado actual

## Uso

1. Ir a "Wizard de Preguntas"
2. Seleccionar una encuesta
3. Crear preguntas usando el formulario
4. Hacer clic en "Agregar Otra Pregunta" para continuar
5. El sistema mantendrá automáticamente la encuesta seleccionada
6. Finalizar cuando se hayan creado todas las preguntas necesarias

## Mantenimiento

Para limpiar sesiones expiradas:

```bash
php artisan wizard:clean-sessions
```

Este comando se puede programar en el cron para ejecutarse automáticamente. 
