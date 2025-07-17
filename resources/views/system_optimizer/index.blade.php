@extends('layouts.app')

@section('title',Optimización del Sistema')

@section(content_header')
    <h1<i class=fas fa-tools"></i> Optimización del Sistema</h1
@stop

@section('content)<div class="row">
    <div class="col-12        <div class="card">
            <div class="card-header>
                <h3 class="card-title">
                    <i class=fas fa-cogs"></i> Herramientas de Optimización
                </h3>
            </div>
            <div class="card-body>
                <div class="row">
                    <!-- Limpiar Cachés -->
                    <div class="col-md-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class=fas fa-broom fa-3x mb-3"></i>
                                <h5 class=card-title>Limpiar Cachés</h5>
                                <p class=card-text>Limpia todas las cachés del sistema</p>
                                <button class=btn btn-light btn-sm" onclick="clearCaches()">
                                    <i class=fas fa-play"></i> Ejecutar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Regenerar Autoloader -->
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class=fas fa-sync fa-3x mb-3"></i>
                                <h5 class="card-title">Regenerar Autoloader</h5>
                                <p class="card-text">Regenera el autoloader de Composer</p>
                                <button class=btn btn-light btn-smonclick=dumpAutoload()                   <i class=fas fa-play"></i> Ejecutar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Optimizar Rutas -->
                    <div class="col-md-6">
                        <div class=cardbg-info text-white">
                            <div class="card-body text-center">
                                <i class=fas fa-route fa-3x mb-3"></i>
                                <h5 class=card-title">Optimizar Rutas</h5>
                                <p class="card-text>Optimiza las rutas del sistema</p>
                                <button class=btn btn-light btn-sm" onclick="optimizeRoutes()">
                                    <i class=fas fa-play"></i> Ejecutar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Limpiar Archivos Temporales -->
                    <div class="col-md-6">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class=fas fa-trash fa-3x mb-3"></i>
                                <h5 class=card-title">Limpiar Temporales</h5>
                                <p class="card-text>Elimina archivos temporales</p>
                                <button class=btn btn-light btn-sm" onclick="clearTempFiles()">
                                    <i class=fas fa-play"></i> Ejecutar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón Optimizar Todo -->
                <div class="row mt-4">
                    <div class="col-12">
                        <button class="btn btn-danger btn-lg" onclick="optimizeAll()">
                            <i class=fas fa-rocket"></i> Optimizar Todo el Sistema
                        </button>
                    </div>
                </div>

                <!-- Resultados -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class=fas fa-list"></i> Resultados de las Operaciones
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="results" class=alert alert-info" style=display: none;                   <i class="fas fa-info-circle"></i> Los resultados aparecerán aquí...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card {
    transition: transform 00.2;
}
.card:hover {
    transform: translateY(-5px);
    transition: all 0.3s;
}
.btn:hover {
    transform: scale(10.05;
}
</style>
@stop

@section('js')
<script>
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML =<i class=fas fa-spinner fa-spin"></i> Procesando...';
    button.disabled = true;
    return originalText;
}

function restoreButton(button, originalText) {
    button.innerHTML = originalText;
    button.disabled = false;
}

function showResults(data, title) {
    let html = `<h6lass="fas fa-check-circle text-success></i> ${title}</h6>`;
    if (typeof data === 'object) {        for (const [key, value] of Object.entries(data)) [object Object]         if (value.success)[object Object]              html += `<div class="text-success><i class=fas fa-check"></i> ${value.message}</div>`;
            } else[object Object]              html += `<div class="text-danger><i class=fas fa-times"></i> ${value.message}</div>`;
            }
        }
    } else[object Object]        html += `<div>${data}</div>`;
    }
    
    $('#results').html(html).removeClass('alert-info alert-success alert-danger).addClass('alert-success').show();
}

function showError(message)[object Object]    $(#results').html(`<i class="fas fa-exclamation-triangle text-danger></i> ${message}`)
        .removeClass('alert-info alert-success alert-danger).addClass('alert-danger').show();
}

function clearCaches() [object Object]    const button = event.target;
    const originalText = showLoading(button);
    
    $.ajax([object Object]    url: {{ route("system.optimizer.clear-caches") }},
        method: 'POST,
        headers: [object Object]           X-CSRF-TOKEN': $(meta[name="csrf-token]').attr(content')
        },
        success: function(response) [object Object]       showResults(response,Limpieza de Cachés Completada');
        },
        error: function(xhr) [object Object]         showError('Error al limpiar cachés: ' + xhr.responseText);
        },
        complete: function() {
            restoreButton(button, originalText);
        }
    });
}

function dumpAutoload() [object Object]    const button = event.target;
    const originalText = showLoading(button);
    
    $.ajax([object Object]    url: {{ route("system.optimizer.dump-autoload") }},
        method: 'POST,
        headers: [object Object]           X-CSRF-TOKEN': $(meta[name="csrf-token]').attr(content')
        },
        success: function(response) [object Object]      if (response.success)[object Object]              showResults(response.message, 'Autoloader Regenerado');
            } else[object Object]              showError(response.message);
            }
        },
        error: function(xhr) [object Object]         showError('Error al regenerar autoloader: ' + xhr.responseText);
        },
        complete: function() {
            restoreButton(button, originalText);
        }
    });
}

function optimizeRoutes() [object Object]    const button = event.target;
    const originalText = showLoading(button);
    
    $.ajax([object Object]    url: {{ route(system.optimizer.optimize-routes") }},
        method: 'POST,
        headers: [object Object]           X-CSRF-TOKEN': $(meta[name="csrf-token]').attr(content')
        },
        success: function(response) [object Object]      if (response.success)[object Object]              showResults(response.message, Rutas Optimizadas');
            } else[object Object]              showError(response.message);
            }
        },
        error: function(xhr) [object Object]         showError('Error al optimizar rutas: ' + xhr.responseText);
        },
        complete: function() {
            restoreButton(button, originalText);
        }
    });
}

function clearTempFiles() [object Object]    const button = event.target;
    const originalText = showLoading(button);
    
    $.ajax([object Object]    url: {{ route("system.optimizer.clear-temp-files") }},
        method: 'POST,
        headers: [object Object]           X-CSRF-TOKEN': $(meta[name="csrf-token]').attr(content')
        },
        success: function(response) [object Object]       showResults(response,Limpieza de Archivos Temporales');
        },
        error: function(xhr) [object Object]         showError('Error al limpiar archivos temporales: ' + xhr.responseText);
        },
        complete: function() {
            restoreButton(button, originalText);
        }
    });
}

function optimizeAll() [object Object]    const button = event.target;
    const originalText = showLoading(button);
    
    $.ajax([object Object]    url: {{ route(system.optimizer.optimize-all") }},
        method: 'POST,
        headers: [object Object]           X-CSRF-TOKEN': $(meta[name="csrf-token]').attr(content')
        },
        success: function(response) [object Object]       showResults(response, 'Optimización Completa del Sistema');
        },
        error: function(xhr) [object Object]         showError(Erroren la optimización completa: ' + xhr.responseText);
        },
        complete: function() {
            restoreButton(button, originalText);
        }
    });
}
</script>
@stop 