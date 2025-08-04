# 🔍 Comandos de Prueba para Cron Job y Sistema de Envío Programado

## 📋 Comandos Disponibles

### 1. **Probar Cron Job Completo**
```bash
php artisan probar:cron-job
```
**Descripción:** Prueba completa del sistema de cron job
- ✅ Verifica conexión a base de datos
- ✅ Muestra configuraciones programadas
- ✅ Lista empleados disponibles
- ✅ Simula verificación de envíos
- ✅ Prueba dispatch de jobs

**Opciones:**
- `--debug`: Muestra información detallada

### 2. **Probar Envío de Correos**
```bash
php artisan probar:envio-correos
```
**Descripción:** Prueba el envío real de correos programados

**Opciones:**
- `--configuracion-id=ID`: Probar configuración específica
- `--test`: Enviar solo correo de prueba
- `--force`: Forzar envío sin verificar fecha/hora

**Ejemplos:**
```bash
# Probar todas las configuraciones
php artisan probar:envio-correos

# Probar configuración específica
php artisan probar:envio-correos --configuracion-id=1

# Enviar correo de prueba
php artisan probar:envio-correos --test

# Forzar envío de configuración específica
php artisan probar:envio-correos --configuracion-id=1 --force
```

### 3. **Verificar Sistema de Colas**
```bash
php artisan verificar:sistema-colas
```
**Descripción:** Verifica el estado del sistema de colas y jobs
- ✅ Verifica tabla de jobs
- ✅ Verifica configuración de colas
- ✅ Lista jobs fallidos
- ✅ Lista jobs pendientes
- ✅ Verifica conexión a Redis

**Opciones:**
- `--fix`: Intenta arreglar problemas encontrados

### 4. **Ejecutar Cron Job Manualmente**
```bash
php artisan ejecutar:cron-job
```
**Descripción:** Ejecuta manualmente el cron job de verificación
- ✅ Busca configuraciones pendientes
- ✅ Verifica fecha/hora de envío
- ✅ Dispatcha jobs automáticamente
- ✅ Actualiza estados de configuraciones

**Opciones:**
- `--force`: Forzar ejecución sin verificar fecha/hora

## 🚀 Flujo de Pruebas Recomendado

### Paso 1: Verificar Sistema Base
```bash
# Verificar conexión y estado general
php artisan probar:cron-job

# Verificar sistema de colas
php artisan verificar:sistema-colas --fix
```

### Paso 2: Probar Envío de Correos
```bash
# Probar correo de prueba
php artisan probar:envio-correos --test

# Probar envío real (forzado)
php artisan probar:envio-correos --force
```

### Paso 3: Ejecutar Cron Job
```bash
# Ejecutar cron job manualmente
php artisan ejecutar:cron-job

# Ejecutar cron job forzado
php artisan ejecutar:cron-job --force
```

## 📊 Información que Proporcionan los Comandos

### Configuraciones Programadas
- Total de configuraciones
- Configuraciones programadas
- Configuraciones pendientes
- Configuraciones activas
- Detalles de cada configuración

### Empleados
- Total de empleados
- Empleados por empresa
- Ejemplos de empleados con correos

### Sistema de Colas
- Estado de tabla jobs
- Jobs fallidos
- Jobs pendientes
- Configuración de driver
- Conexión a Redis (si aplica)

### Envíos Programados
- Fecha/hora programada vs actual
- Estado de cada configuración
- Destinatarios configurados
- Resultado de dispatch de jobs

## 🔧 Solución de Problemas

### Error de Conexión a Base de Datos
```bash
# Verificar configuración
php artisan config:show database

# Probar conexión
php artisan tinker --execute="DB::connection()->getPdo();"
```

### Jobs No Se Ejecutan
```bash
# Verificar worker de colas
php artisan queue:work --verbose

# Verificar jobs fallidos
php artisan queue:failed

# Limpiar jobs fallidos
php artisan queue:flush
```

### Tablas Faltantes
```bash
# Crear tabla de jobs
php artisan queue:table
php artisan migrate

# Crear tabla de jobs fallidos
php artisan queue:failed-table
php artisan migrate
```

## 📝 Logs y Monitoreo

### Ver Logs de Laravel
```bash
# Ver logs de la aplicación
tail -f storage/logs/laravel.log

# Ver logs específicos de jobs
grep "EnviarCorreosProgramados" storage/logs/laravel.log
```

### Monitorear Jobs en Tiempo Real
```bash
# Worker de colas en modo verbose
php artisan queue:work --verbose --timeout=60

# Ver jobs pendientes
php artisan queue:monitor
```

## ⚡ Comandos Rápidos para Diagnóstico

```bash
# Diagnóstico completo en un comando
php artisan probar:cron-job && php artisan verificar:sistema-colas && php artisan ejecutar:cron-job --force

# Verificar estado actual
php artisan tinker --execute="echo 'Configuraciones: ' . App\Models\ConfiguracionEnvio::count(); echo 'Jobs: ' . DB::table('jobs')->count();"

# Limpiar y reiniciar
php artisan queue:flush && php artisan queue:restart
```

## 🎯 Casos de Uso Específicos

### Probar Configuración Nueva
```bash
# 1. Crear configuración programada
# 2. Verificar que se guardó
php artisan probar:cron-job

# 3. Probar envío de prueba
php artisan probar:envio-correos --configuracion-id=1 --test

# 4. Forzar envío real
php artisan probar:envio-correos --configuracion-id=1 --force
```

### Debuggear Jobs Fallidos
```bash
# 1. Ver jobs fallidos
php artisan verificar:sistema-colas

# 2. Ver detalles del error
php artisan queue:failed

# 3. Reintentar job específico
php artisan queue:retry {id}
```

### Verificar Cron Job Automático
```bash
# 1. Simular ejecución del cron
php artisan ejecutar:cron-job

# 2. Verificar que se crearon jobs
php artisan verificar:sistema-colas

# 3. Ejecutar worker para procesar jobs
php artisan queue:work
```

## 📞 Soporte

Si encuentras problemas con estos comandos:

1. **Verifica la conexión a la base de datos**
2. **Revisa los logs en `storage/logs/laravel.log`**
3. **Asegúrate de que las tablas necesarias existen**
4. **Verifica que el worker de colas esté ejecutándose**

Los comandos están diseñados para ser informativos y ayudar a diagnosticar problemas rápidamente. 
