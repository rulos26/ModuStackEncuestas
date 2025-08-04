# 🚀 Sistema de Envío Programado de Encuestas

## 📋 Resumen de Cambios Implementados

Se ha implementado un sistema completo de envío programado de encuestas con las siguientes características:

### ✅ **Funcionalidades Implementadas:**

1. **Dos Modos de Envío:**
   - **Manual:** Finaliza el proceso una vez la encuesta esté lista
   - **Programado:** Permite configurar fecha, hora y destinatarios

2. **Configuración de Envío Programado:**
   - Fecha y hora de inicio del envío
   - Tipo de destinatario (empleados, clientes, proveedores, lista personalizada)
   - Cálculo automático de bloques sugeridos (si hay más de 50 destinatarios)
   - Modo debug/prueba con correo de prueba

3. **Sistema de Jobs y Cron:**
   - Job `EnviarCorreosProgramados` para procesar envíos
   - Comando `VerificarEnvioProgramado` que se ejecuta cada minuto
   - Publicación automática de encuestas al completar el envío

4. **Wizard de Configuración:**
   - Interfaz intuitiva con pasos claros
   - Validación en tiempo real
   - Vista previa de configuración
   - Envío de correos de prueba

## 🛠️ **Instalación y Configuración**

### **1. Ejecutar Migraciones**

```bash
php artisan migrate
```

### **2. Configurar Cron Job**

Agregar la siguiente línea al crontab del servidor:

```bash
# Editar crontab
crontab -e

# Agregar esta línea
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### **3. Configurar Queue Worker**

```bash
# Iniciar queue worker
php artisan queue:work

# O para producción, usar supervisor
```

### **4. Verificar Configuración**

```bash
# Probar el comando manualmente
php artisan encuestas:verificar-envio-programado

# Verificar que el schedule está configurado
php artisan schedule:list
```

## 📊 **Estructura de Base de Datos**

### **Tabla: `configuracion_envios`**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único |
| `empresa_id` | bigint | ID de la empresa |
| `encuesta_id` | bigint | ID de la encuesta |
| `nombre_remitente` | string | Nombre del remitente |
| `correo_remitente` | string | Email del remitente |
| `asunto` | string | Asunto del correo |
| `cuerpo_mensaje` | text | Contenido del correo |
| `tipo_envio` | enum | 'manual' o 'programado' |
| `plantilla` | string | Nombre de plantilla (opcional) |
| `activo` | boolean | Si la configuración está activa |
| `fecha_envio` | date | Fecha de envío (programado) |
| `hora_envio` | time | Hora de envío (programado) |
| `tipo_destinatario` | enum | Tipo de destinatarios |
| `numero_bloques` | integer | Número de bloques de envío |
| `correo_prueba` | string | Email para pruebas |
| `modo_prueba` | boolean | Si está en modo prueba |
| `estado_programacion` | enum | Estado del envío programado |

## 🔄 **Flujo de Trabajo**

### **Envío Manual:**
1. Usuario selecciona "Manual"
2. Configura datos básicos del correo
3. Guarda configuración
4. La encuesta se marca como lista para envío manual

### **Envío Programado:**
1. Usuario selecciona "Programado"
2. Configura fecha, hora y destinatarios
3. Sistema sugiere número de bloques
4. Usuario puede enviar correo de prueba
5. Al guardar, se programa el envío
6. Cron job verifica cada minuto
7. Job procesa envíos cuando llega la hora
8. Encuesta se publica automáticamente

## 📁 **Archivos Creados/Modificados**

### **Modelos:**
- `app/Models/ConfiguracionEnvio.php` - Actualizado con nuevos campos y métodos

### **Controladores:**
- `app/Http/Controllers/ConfiguracionEnvioController.php` - Actualizado con wizard

### **Jobs:**
- `app/Jobs/EnviarCorreosProgramados.php` - Nuevo job para envíos programados

### **Comandos:**
- `app/Console/Commands/VerificarEnvioProgramado.php` - Comando para verificar envíos

### **Vistas:**
- `resources/views/configuracion_envio/configurar.blade.php` - Wizard actualizado

### **Migraciones:**
- `database/migrations/2025_08_04_120000_update_configuracion_envios_add_programado_fields.php`

### **Rutas:**
- `routes/web.php` - Agregada ruta para envío de prueba

## ⚙️ **Configuración del Servidor**

### **Para Producción:**

1. **Configurar Supervisor para Queue Workers:**
```bash
# Instalar supervisor
sudo apt-get install supervisor

# Crear configuración
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

2. **Reiniciar Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

3. **Configurar Logs:**
```bash
# Crear directorio de logs
mkdir -p /var/log/laravel

# Configurar permisos
chown -R www-data:www-data /var/log/laravel
```

## 🧪 **Pruebas**

### **Probar Envío de Prueba:**
1. Ir a configuración de envío
2. Seleccionar "Programado"
3. Configurar correo de prueba
4. Hacer clic en "Enviar Correo de Prueba"

### **Probar Envío Programado:**
1. Configurar envío para 2-3 minutos en el futuro
2. Esperar a que se ejecute el cron job
3. Verificar logs en `storage/logs/laravel.log`

### **Verificar Estado:**
```bash
# Ver configuraciones programadas
php artisan tinker
>>> App\Models\ConfiguracionEnvio::programadasPendientes()->get()

# Ver jobs en cola
php artisan queue:work --once
```

## 🔍 **Monitoreo y Logs**

### **Logs Importantes:**
- `storage/logs/laravel.log` - Logs generales de Laravel
- `storage/logs/worker.log` - Logs de queue workers (si usa supervisor)

### **Comandos de Monitoreo:**
```bash
# Ver jobs en cola
php artisan queue:monitor

# Ver schedule
php artisan schedule:list

# Ver logs en tiempo real
tail -f storage/logs/laravel.log
```

## 🚨 **Solución de Problemas**

### **Problema: Los envíos no se ejecutan**
1. Verificar que el cron job esté configurado
2. Verificar que el queue worker esté ejecutándose
3. Revisar logs de Laravel

### **Problema: Correos no llegan**
1. Verificar configuración de SMTP
2. Revisar logs de correo
3. Probar con correo de prueba

### **Problema: Error en jobs**
1. Verificar permisos de archivos
2. Revisar configuración de base de datos
3. Verificar que las tablas existan

## 📈 **Escalabilidad**

El sistema está diseñado para ser escalable:

- **Jobs en cola:** Permite procesar múltiples envíos simultáneamente
- **Bloques de envío:** Divide envíos grandes en bloques manejables
- **Pausas entre envíos:** Evita sobrecarga del servidor de correo
- **Logs detallados:** Facilita el debugging y monitoreo

## 🔐 **Seguridad**

- Validación de entrada en todos los formularios
- Sanitización de datos antes de guardar
- Logs de auditoría para todos los envíos
- Verificación de permisos de usuario
- Protección CSRF en todos los formularios 
