# Wizard de Lógica de Preguntas

## 📋 Descripción General

El **Wizard de Lógica de Preguntas** es un módulo administrativo que permite configurar la lógica de salto condicional entre preguntas dentro de una encuesta. Este wizard facilita la creación de flujos dinámicos donde las respuestas del usuario determinan qué preguntas se muestran a continuación.

## 🎯 Características Principales

### ✅ Funcionalidades Implementadas

1. **Selección de Encuesta**
   - Interfaz limpia para seleccionar la encuesta a configurar
   - Solo muestra encuestas con preguntas que tienen respuestas configuradas
   - Validación automática de disponibilidad

2. **Configuración de Lógica por Pregunta**
   - Progreso visual del wizard (pregunta X de Y)
   - Configuración individual para cada respuesta de la pregunta
   - Opciones de salto: continuar secuencialmente, saltar a pregunta específica, o finalizar encuesta
   - Vista previa en tiempo real de la lógica configurada

3. **Validación Inteligente**
   - Prevención de bucles lógicos
   - Validación de coherencia (no finalizar y saltar simultáneamente)
   - Verificación de pertenencia de preguntas y respuestas a la encuesta

4. **Resumen y Confirmación**
   - Vista completa de todas las lógicas configuradas
   - Estadísticas de saltos y finalizaciones
   - Confirmación final antes de guardar

5. **Gestión de Sesión**
   - Persistencia de estado entre pasos
   - Sincronización con cookies para mayor estabilidad
   - Limpieza automática de sesiones expiradas

## 🏗️ Arquitectura Técnica

### Controlador Principal
- **`LogicaWizardController`**: Maneja toda la lógica del wizard
  - `index()`: Selección de encuesta
  - `configurar()`: Configuración de lógica por pregunta
  - `store()`: Guardado de lógica configurada
  - `resumen()`: Vista de resumen final
  - `confirmar()`: Confirmación y finalización
  - `cancel()`: Cancelación del wizard

### Middleware de Sesión
- **`LogicaWizardSessionMiddleware`**: Gestiona la persistencia de estado
  - Sincronización entre sesión y cookies
  - Variables: `wizard_encuesta_id`, `wizard_pregunta_index`, `wizard_logica_count`

### Modelos Utilizados
- **`Encuesta`**: Encuesta seleccionada
- **`Pregunta`**: Preguntas de la encuesta
- **`Respuesta`**: Respuestas de cada pregunta
- **`Logica`**: Configuración de lógica de salto

## 📁 Estructura de Archivos

```
app/
├── Http/
│   ├── Controllers/
│   │   └── LogicaWizardController.php
│   └── Middleware/
│       └── LogicaWizardSessionMiddleware.php
├── Console/Commands/
│   └── CleanExpiredLogicaWizardSessions.php
└── Models/
    ├── Encuesta.php
    ├── Pregunta.php
    ├── Respuesta.php
    └── Logica.php

resources/views/logica/wizard/
├── index.blade.php
├── configurar.blade.php
└── resumen.blade.php

config/
└── adminlte.php (menú agregado)

routes/
└── web.php (rutas del wizard)
```

## 🔄 Flujo de Trabajo

### 1. Selección de Encuesta
```
Usuario → Selecciona encuesta → Valida disponibilidad → Continúa al wizard
```

### 2. Configuración de Lógica
```
Para cada pregunta con respuestas:
├── Mostrar pregunta actual
├── Configurar lógica por respuesta
├── Vista previa en tiempo real
├── Validar coherencia
└── Guardar y continuar
```

### 3. Resumen y Confirmación
```
Mostrar todas las lógicas → Estadísticas → Confirmar → Finalizar
```

## 🎨 Interfaz de Usuario

### Diseño Responsivo
- Compatible con escritorio y dispositivos móviles
- Interfaz limpia y profesional
- Elementos visuales intuitivos (iconos, badges, colores)

### Componentes Visuales
- **Barra de progreso**: Muestra el avance del wizard
- **Vista previa**: Actualización en tiempo real de la lógica
- **Estadísticas**: Contadores de saltos y finalizaciones
- **Diagrama de flujo**: Representación visual de las relaciones

### Validación Visual
- Alertas de error en tiempo real
- Confirmaciones antes de acciones importantes
- Feedback visual de cambios

## 🔧 Configuración y Uso

### Acceso al Módulo
1. Ir a **Gestión de Encuestas** → **Configurar Lógica de Salto**
2. Seleccionar la encuesta deseada
3. Seguir el wizard paso a paso

### Tipos de Preguntas Soportados
- **Selección Única**: `seleccion_unica`
- **Casillas de Verificación**: `casillas_verificacion`
- **Selección Múltiple**: `seleccion_multiple`

### Opciones de Lógica
1. **Continuar Secuencialmente**: Siguiente pregunta en orden
2. **Saltar a Pregunta Específica**: Ir a pregunta determinada
3. **Finalizar Encuesta**: Terminar la encuesta inmediatamente

## 🛠️ Comandos Artisan

### Limpieza de Sesiones
```bash
# Limpiar sesiones expiradas (por defecto 1 día)
php artisan wizard:clean-logica-sessions

# Limpiar sesiones de más de 3 días
php artisan wizard:clean-logica-sessions --days=3
```

## 🔒 Seguridad y Validación

### Validaciones Implementadas
- **Autenticación**: Solo usuarios autenticados
- **Propiedad**: Verificación de pertenencia de encuestas
- **Coherencia**: Prevención de configuraciones contradictorias
- **Integridad**: Validación de relaciones entre entidades

### Prevención de Errores
- **Buclos Lógicos**: Validación de saltos circulares
- **Datos Inválidos**: Verificación de existencia de preguntas/respuestas
- **Configuraciones Contradictorias**: No permitir finalizar y saltar simultáneamente

## 📊 Base de Datos

### Tabla `logicas`
```sql
CREATE TABLE logicas (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pregunta_id BIGINT UNSIGNED NOT NULL,           -- Pregunta origen
    respuesta_id BIGINT UNSIGNED NOT NULL,          -- Respuesta que activa
    siguiente_pregunta_id BIGINT UNSIGNED NULL,     -- Pregunta destino
    finalizar BOOLEAN DEFAULT FALSE,                -- Finalizar encuesta
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE,
    FOREIGN KEY (respuesta_id) REFERENCES respuestas(id) ON DELETE CASCADE,
    FOREIGN KEY (siguiente_pregunta_id) REFERENCES preguntas(id) ON DELETE SET NULL
);
```

## 🚀 Rutas Disponibles

```php
// Rutas del Wizard de Lógica
Route::middleware(['auth', 'logica.wizard.session'])->group(function () {
    Route::get('logica/wizard', [LogicaWizardController::class, 'index'])->name('logica.wizard.index');
    Route::get('logica/wizard/configurar', [LogicaWizardController::class, 'configurar'])->name('logica.wizard.configurar');
    Route::post('logica/wizard/store', [LogicaWizardController::class, 'store'])->name('logica.wizard.store');
    Route::get('logica/wizard/resumen', [LogicaWizardController::class, 'resumen'])->name('logica.wizard.resumen');
    Route::post('logica/wizard/confirmar', [LogicaWizardController::class, 'confirmar'])->name('logica.wizard.confirmar');
    Route::get('logica/wizard/cancel', [LogicaWizardController::class, 'cancel'])->name('logica.wizard.cancel');
});
```

## 📝 Logs y Monitoreo

### Logs Generados
- Acceso al wizard
- Configuración de lógica
- Errores de validación
- Finalización del proceso
- Cancelaciones

### Información Registrada
- ID de usuario
- ID de encuesta
- Pregunta actual
- Lógicas creadas
- Errores y excepciones

## 🔄 Integración con el Sistema

### Menú de Navegación
- Agregado en **Gestión de Encuestas** → **Configurar Lógica de Salto**
- Icono: `fas fa-project-diagram`
- Ruta: `logica.wizard.index`

### Dependencias
- **AdminLTE**: Framework de interfaz
- **FontAwesome**: Iconos
- **Bootstrap**: Componentes CSS
- **jQuery**: Funcionalidad JavaScript

## 🎯 Casos de Uso

### Ejemplo 1: Encuesta de Satisfacción
```
Pregunta 1: "¿Está satisfecho con nuestro servicio?"
- Respuesta "Sí" → Continuar a Pregunta 2
- Respuesta "No" → Saltar a Pregunta 5 (pregunta de mejora)

Pregunta 5: "¿Qué aspectos mejoraríamos?"
- Cualquier respuesta → Finalizar encuesta
```

### Ejemplo 2: Encuesta de Producto
```
Pregunta 1: "¿Ha comprado nuestro producto?"
- Respuesta "Sí" → Saltar a Pregunta 3 (experiencia de compra)
- Respuesta "No" → Continuar a Pregunta 2 (razones)

Pregunta 2: "¿Por qué no ha comprado?"
- Respuesta "Precio alto" → Saltar a Pregunta 4 (precios)
- Respuesta "No lo necesito" → Finalizar encuesta
```

## 🔮 Mejoras Futuras

### Funcionalidades Planificadas
1. **Editor Visual**: Diagrama de flujo interactivo
2. **Condiciones Complejas**: Múltiples respuestas para activar lógica
3. **Plantillas**: Lógicas predefinidas para casos comunes
4. **Importación/Exportación**: Compartir configuraciones de lógica
5. **Análisis de Flujo**: Estadísticas de uso de cada salto

### Optimizaciones Técnicas
1. **Caché**: Almacenamiento en caché de lógicas frecuentes
2. **Validación Avanzada**: Detección automática de bucles complejos
3. **API REST**: Endpoints para integración externa
4. **Webhooks**: Notificaciones de cambios en lógica

## 📞 Soporte y Mantenimiento

### Comandos de Mantenimiento
```bash
# Limpiar sesiones expiradas
php artisan wizard:clean-logica-sessions

# Verificar integridad de lógicas
php artisan tinker
>>> App\Models\Logica::whereDoesntHave('pregunta')->delete();
>>> App\Models\Logica::whereDoesntHave('respuesta')->delete();
```

### Monitoreo Recomendado
- Revisar logs de errores regularmente
- Monitorear uso de sesiones
- Verificar integridad de datos periódicamente
- Backup de configuraciones de lógica importantes

---

**Desarrollado para ModuStack Encuestas**  
*Sistema de Gestión de Encuestas Profesional* 
