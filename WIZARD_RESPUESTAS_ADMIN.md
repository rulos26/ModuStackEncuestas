# Wizard de Configuración de Respuestas - Uso Administrativo

## 📋 Descripción General

El **Wizard de Configuración de Respuestas** es un módulo administrativo diseñado para que los administradores del sistema configuren las **respuestas concretas** que estarán disponibles para las preguntas de tipo "Selección Única" y "Casillas de Verificación" en las encuestas.

### 🎯 Propósito Principal

- **NO** es para que los usuarios respondan encuestas
- **SÍ** es para que los administradores definan las opciones de respuesta disponibles
- Permite configurar las respuestas predefinidas que verán los usuarios finales

## 🔄 Flujo de Trabajo

### 1. **Selección de Encuesta**
- Lista encuestas que tienen preguntas sin respuestas configuradas
- Filtra automáticamente preguntas de tipo "Selección Única" y "Casillas de Verificación"
- Muestra estadísticas de preguntas pendientes

### 2. **Configuración de Respuestas**
- Muestra una pregunta por pantalla
- Permite agregar múltiples opciones de respuesta
- Cada opción tiene texto y orden
- Validación para evitar duplicados

### 3. **Resumen y Confirmación**
- Muestra todas las preguntas configuradas
- Lista las opciones de respuesta agregadas
- Permite finalizar o continuar con otra encuesta

## 🛠️ Características Técnicas

### **Controlador Principal**
- `RespuestaWizardController.php` - Maneja toda la lógica del wizard

### **Vistas**
- `index.blade.php` - Lista de encuestas que necesitan configuración
- `responder.blade.php` - Formulario para agregar respuestas
- `resumen.blade.php` - Resumen de configuración completada

### **Middleware**
- `RespuestaWizardSessionMiddleware.php` - Maneja el estado de la sesión

### **Comando de Limpieza**
- `CleanExpiredRespuestaWizardSessions.php` - Limpia sesiones expiradas

## 📊 Funcionalidades

### **Gestión de Sesiones**
- Mantiene estado entre pasos del wizard
- Persistencia en cookies como respaldo
- Limpieza automática de sesiones expiradas

### **Validaciones**
- Al menos una opción de respuesta por pregunta
- No permite textos duplicados
- Validación de campos requeridos

### **Interfaz de Usuario**
- Diseño responsive y moderno
- Indicadores de progreso visual
- Mensajes informativos claros
- Confirmaciones antes de acciones críticas

## 🔧 Configuración

### **Rutas**
```php
Route::middleware(['auth', 'respuesta.wizard.session'])->group(function () {
    Route::get('respuestas/wizard', [RespuestaWizardController::class, 'index'])->name('respuestas.wizard.index');
    Route::get('respuestas/wizard/responder', [RespuestaWizardController::class, 'responder'])->name('respuestas.wizard.responder');
    Route::post('respuestas/wizard/store', [RespuestaWizardController::class, 'store'])->name('respuestas.wizard.store');
    Route::get('respuestas/wizard/resumen', [RespuestaWizardController::class, 'resumen'])->name('respuestas.wizard.resumen');
    Route::post('respuestas/wizard/confirmar', [RespuestaWizardController::class, 'confirmar'])->name('respuestas.wizard.confirmar');
    Route::get('respuestas/wizard/cancel', [RespuestaWizardController::class, 'cancel'])->name('respuestas.wizard.cancel');
});
```

### **Menú**
- Ubicado en "Gestión de Encuestas" → "Configurar Respuestas"
- Icono: `fas fa-cogs`
- Acceso directo para administradores

## 📝 Uso del Sistema

### **Para Administradores**

1. **Acceder al Wizard**
   - Ir a "Gestión de Encuestas" → "Configurar Respuestas"
   - Ver lista de encuestas que necesitan configuración

2. **Seleccionar Encuesta**
   - Elegir una encuesta de la lista
   - Ver estadísticas de preguntas pendientes

3. **Configurar Respuestas**
   - Para cada pregunta, agregar opciones de respuesta
   - Definir orden de las opciones
   - Agregar múltiples opciones según sea necesario

4. **Finalizar**
   - Revisar resumen de configuración
   - Confirmar y finalizar el proceso

### **Tipos de Pregunta Soportados**

- **Selección Única**: Los usuarios pueden elegir una sola opción
- **Casillas de Verificación**: Los usuarios pueden elegir múltiples opciones

### **Tipos NO Soportados**

- Respuesta corta
- Párrafo
- Escala lineal
- Fecha
- Hora

## 🔍 Monitoreo y Logs

### **Logs Generados**
- Acceso al wizard
- Configuración de respuestas
- Finalización del proceso
- Errores y excepciones

### **Comando de Limpieza**
```bash
php artisan wizard:clean-respuesta-sessions
```

## 🚀 Ventajas del Sistema

### **Para Administradores**
- Interfaz intuitiva y fácil de usar
- Proceso paso a paso guiado
- Validaciones automáticas
- Persistencia de sesión

### **Para Usuarios Finales**
- Opciones de respuesta claras y organizadas
- Mejor experiencia de usuario
- Respuestas consistentes

### **Para el Sistema**
- Datos estructurados y validados
- Trazabilidad completa
- Mantenimiento simplificado

## 🔧 Mantenimiento

### **Limpieza Regular**
- Ejecutar comando de limpieza semanalmente
- Revisar logs de errores
- Monitorear uso del sistema

### **Actualizaciones**
- Mantener documentación actualizada
- Revisar validaciones según necesidades
- Optimizar consultas de base de datos

## 📞 Soporte

Para dudas o problemas con el wizard:
1. Revisar logs del sistema
2. Verificar configuración de middleware
3. Comprobar permisos de usuario
4. Contactar al equipo de desarrollo

---

**Nota**: Este wizard es exclusivamente para uso administrativo. Los usuarios finales responden las encuestas a través de otros módulos del sistema. 
