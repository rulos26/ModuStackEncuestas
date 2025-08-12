# Módulo Wizard de Respuestas

## Descripción General

El módulo Wizard de Respuestas es una herramienta completa y profesional para la gestión de respuestas de encuestas, diseñado siguiendo la lógica y estilo visual del módulo de preguntas existente. Proporciona una experiencia de usuario fluida y moderna para responder encuestas de manera eficiente.

## Características Principales

### ✅ **Flujo Funcional Completo**
- **Selección de encuesta**: Listado limpio y profesional de encuestas activas
- **Respuesta progresiva**: Preguntas presentadas una por una en orden
- **Validación inteligente**: Adaptada según el tipo de pregunta
- **Guardado automático**: Respuestas parciales guardadas automáticamente
- **Resumen detallado**: Vista completa de todas las respuestas
- **Confirmación final**: Opciones para finalizar o continuar con otra encuesta

### ✅ **Tipos de Preguntas Soportados**
- **Respuesta corta**: Texto de una línea con validación de caracteres
- **Párrafo**: Texto multilínea con límites configurables
- **Selección única**: Opciones de radio con una sola selección
- **Casillas de verificación**: Múltiples selecciones permitidas
- **Escala lineal**: Rango numérico con etiquetas personalizables
- **Fecha**: Selector de fecha nativo
- **Hora**: Selector de hora nativo

### ✅ **Experiencia de Usuario**
- **Interfaz responsive**: Compatible con escritorio y dispositivos móviles
- **Indicadores de progreso**: Barra visual del avance del wizard
- **Navegación intuitiva**: Breadcrumbs y botones claros
- **Validación en tiempo real**: Mensajes de error inmediatos
- **Diseño moderno**: Consistente con el sistema existente

## Estructura del Proyecto

### 📁 **Controladores**
- `app/Http/Controllers/RespuestaWizardController.php` - Lógica principal del wizard

### 📁 **Middleware**
- `app/Http/Middleware/RespuestaWizardSessionMiddleware.php` - Gestión de estado

### 📁 **Vistas**
- `resources/views/respuestas/wizard/index.blade.php` - Selección de encuesta
- `resources/views/respuestas/wizard/responder.blade.php` - Formulario de respuesta
- `resources/views/respuestas/wizard/resumen.blade.php` - Resumen de respuestas

### 📁 **Comandos**
- `app/Console/Commands/CleanExpiredRespuestaWizardSessions.php` - Limpieza de sesiones

### 📁 **Rutas**
- Registradas en `routes/web.php` con middleware de autenticación

## Flujo de Funcionamiento

### 1. **Selección de Encuesta** (Paso 1)
```
Usuario → Listado de encuestas → Selecciona encuesta → Inicia wizard
```

**Características:**
- Muestra solo encuestas publicadas y habilitadas
- Información detallada de cada encuesta
- Indicador de sesión activa si existe
- Diseño de tarjetas con hover effects

### 2. **Respuesta de Preguntas** (Paso 2)
```
Pregunta actual → Formulario adaptativo → Validación → Guardado → Siguiente
```

**Características:**
- Progreso visual (X de Y preguntas)
- Campos adaptados al tipo de pregunta
- Validación según reglas configuradas
- Guardado automático en cada paso
- Navegación fluida entre preguntas

### 3. **Resumen de Respuestas** (Paso 3)
```
Todas las respuestas → Tabla resumen → Estadísticas → Opciones de continuación
```

**Características:**
- Tabla detallada con todas las respuestas
- Estadísticas de completitud
- Información de la sesión
- Opciones para finalizar o continuar

### 4. **Confirmación y Finalización** (Paso 4-5)
```
Confirmar respuestas → Guardar definitivamente → Mensaje de éxito → Redirección
```

**Opciones disponibles:**
- **Finalizar y salir**: Regresa al listado de encuestas
- **Iniciar otra encuesta**: Limpia sesión y permite nueva selección

## Configuración Técnica

### **Middleware Registrado**
```php
'respuesta.wizard.session' => \App\Http\Middleware\RespuestaWizardSessionMiddleware::class,
```

### **Rutas Protegidas**
```php
Route::middleware(['auth', 'respuesta.wizard.session'])->group(function () {
    Route::get('respuestas/wizard', [RespuestaWizardController::class, 'index']);
    Route::get('respuestas/wizard/responder', [RespuestaWizardController::class, 'responder']);
    Route::post('respuestas/wizard/store', [RespuestaWizardController::class, 'store']);
    Route::get('respuestas/wizard/resumen', [RespuestaWizardController::class, 'resumen']);
    Route::post('respuestas/wizard/confirmar', [RespuestaWizardController::class, 'confirmar']);
    Route::get('respuestas/wizard/cancel', [RespuestaWizardController::class, 'cancel']);
});
```

### **Gestión de Estado**
- **Sesiones**: Estado principal del wizard
- **Cookies**: Respaldo para persistencia
- **Base de datos**: Almacenamiento definitivo de respuestas

## Validaciones Implementadas

### **Por Tipo de Pregunta**

#### Respuesta Corta / Párrafo
- Campo obligatorio si está marcado
- Límites de caracteres (mínimo/máximo)
- Validación de longitud

#### Selección Única
- Una opción obligatoria seleccionada
- Validación de existencia en base de datos

#### Casillas de Verificación
- Al menos una opción seleccionada
- Validación de opciones válidas

#### Escala Lineal
- Valor dentro del rango configurado
- Validación de tipo numérico

#### Fecha / Hora
- Formato válido
- Campo obligatorio si está marcado

## Características Avanzadas

### **Persistencia de Datos**
- Guardado automático de respuestas parciales
- Recuperación de sesión en caso de interrupción
- Limpieza automática de sesiones expiradas

### **Seguridad**
- Validación de permisos de usuario
- Verificación de estado de encuesta
- Protección CSRF en formularios
- Logging de actividades

### **Rendimiento**
- Carga optimizada de datos
- Consultas eficientes a base de datos
- Caché de sesiones

### **Mantenimiento**
- Comando de limpieza de sesiones expiradas
- Logs detallados para debugging
- Manejo de errores robusto

## Uso del Sistema

### **Para Usuarios Finales**

1. **Acceder al Wizard**
   ```
   Navegar a: Respuestas → Wizard de Respuestas
   ```

2. **Seleccionar Encuesta**
   ```
   Ver listado → Elegir encuesta → Comenzar a responder
   ```

3. **Responder Preguntas**
   ```
   Leer pregunta → Completar campo → Validar → Siguiente
   ```

4. **Revisar Resumen**
   ```
   Ver todas las respuestas → Confirmar → Finalizar
   ```

### **Para Administradores**

1. **Monitoreo**
   ```
   Ver logs de actividad
   Revisar respuestas guardadas
   Monitorear sesiones activas
   ```

2. **Mantenimiento**
   ```bash
   # Limpiar sesiones expiradas
   php artisan respuesta-wizard:clean-sessions
   ```

3. **Configuración**
   ```
   Ajustar tiempo de expiración de sesiones
   Configurar validaciones
   Personalizar mensajes
   ```

## Beneficios del Sistema

### ✅ **Para Usuarios**
- **Experiencia fluida**: Navegación intuitiva y rápida
- **Progreso visible**: Indicadores claros del avance
- **Validación inmediata**: Feedback instantáneo
- **Persistencia**: No se pierden datos por interrupciones
- **Responsive**: Funciona en cualquier dispositivo

### ✅ **Para Administradores**
- **Gestión eficiente**: Proceso automatizado
- **Datos confiables**: Validación robusta
- **Monitoreo completo**: Logs y estadísticas
- **Mantenimiento simple**: Comandos automatizados
- **Escalabilidad**: Arquitectura preparada para crecimiento

### ✅ **Para el Sistema**
- **Consistencia**: Diseño unificado con el resto
- **Rendimiento**: Optimizado para carga eficiente
- **Seguridad**: Múltiples capas de protección
- **Mantenibilidad**: Código limpio y documentado
- **Extensibilidad**: Fácil agregar nuevas funcionalidades

## Comandos Disponibles

### **Limpieza de Sesiones**
```bash
php artisan respuesta-wizard:clean-sessions
```

**Funcionalidad:**
- Elimina sesiones expiradas (más de 2 horas)
- Registra actividad en logs
- Optimiza rendimiento del sistema

## Logs y Monitoreo

### **Eventos Registrados**
- Acceso al wizard
- Guardado de respuestas
- Finalización de encuestas
- Errores y excepciones
- Limpieza de sesiones

### **Información Capturada**
- ID de usuario
- ID de encuesta
- Tipo de pregunta
- Timestamp de actividad
- IP address
- User agent

## Consideraciones Técnicas

### **Base de Datos**
- Tabla `respuestas_usuario` para almacenar respuestas
- Relaciones con `encuestas` y `preguntas`
- Índices optimizados para consultas

### **Sesiones**
- Tiempo de expiración: 2 horas
- Respaldo en cookies
- Limpieza automática

### **Validaciones**
- Lado cliente (JavaScript)
- Lado servidor (Laravel)
- Base de datos (Constraints)

## Roadmap Futuro

### **Funcionalidades Planificadas**
- [ ] Exportación de respuestas
- [ ] Análisis en tiempo real
- [ ] Notificaciones push
- [ ] Integración con IA
- [ ] Modo offline
- [ ] Multiidioma

### **Mejoras Técnicas**
- [ ] API REST completa
- [ ] WebSockets para tiempo real
- [ ] Caché avanzado
- [ ] Optimización de consultas
- [ ] Tests automatizados

## Soporte y Mantenimiento

### **Documentación**
- Código comentado
- Documentación técnica
- Guías de usuario
- Ejemplos de uso

### **Monitoreo**
- Logs detallados
- Métricas de rendimiento
- Alertas automáticas
- Dashboard de estado

### **Actualizaciones**
- Versiones regulares
- Parches de seguridad
- Nuevas funcionalidades
- Mejoras de rendimiento

---

**Desarrollado con ❤️ para proporcionar la mejor experiencia de respuesta de encuestas.** 
