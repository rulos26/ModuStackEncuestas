@extends('adminlte::page')

@section('title', 'Gestión de Seeders')

@section('content_header')
    <h1><i class="fas fa-seedling"></i> Gestión de Seeders</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Ejecutar Seeders</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Seeder Principal -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-2x mb-2"></i>
                                <h6>Seeder Principal</h6>
                                <p class="card-text">Ejecuta todos los seeders en orden</p>
                                <form method="POST" action="{{ route('system.tools.seeders') }}">
                                    @csrf
                                    <input type="hidden" name="seeder" value="all">
                                    <button type="submit" name="ejecutar" class="btn btn-light btn-sm"
                                            onclick="return confirm('¿Estás seguro de ejecutar todos los seeders?')">
                                        <i class="fas fa-play"></i> Ejecutar Todo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Seeder de Usuarios -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h6>Seeder de Usuarios</h6>
                                <p class="card-text">Crea usuarios de prueba</p>
                                <form method="POST" action="{{ route('system.tools.seeders') }}">
                                    @csrf
                                    <input type="hidden" name="seeder" value="UserSeeder">
                                    <button type="submit" name="ejecutar" class="btn btn-light btn-sm">
                                        <i class="fas fa-user-plus"></i> Crear Usuarios
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Seeder de Roles -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-info">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tag fa-2x mb-2"></i>
                                <h6>Seeder de Roles</h6>
                                <p class="card-text">Crea roles y permisos</p>
                                <form method="POST" action="{{ route('system.tools.seeders') }}">
                                    @csrf
                                    <input type="hidden" name="seeder" value="roleSeeder">
                                    <button type="submit" name="ejecutar" class="btn btn-light btn-sm">
                                        <i class="fas fa-tags"></i> Crear Roles
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Seeder de Empresas -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-building fa-2x mb-2"></i>
                                <h6>Seeder de Empresas</h6>
                                <p class="card-text">Crea empresas de prueba</p>
                                <form method="POST" action="{{ route('system.tools.seeders') }}">
                                    @csrf
                                    <input type="hidden" name="seeder" value="EmpresaSeeder">
                                    <button type="submit" name="ejecutar" class="btn btn-light btn-sm">
                                        <i class="fas fa-building"></i> Crear Empresas
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Seeder de Tokens -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-secondary">
                            <div class="card-body text-center">
                                <i class="fas fa-key fa-2x mb-2"></i>
                                <h6>Seeder de Tokens</h6>
                                <p class="card-text">Crea tokens de prueba</p>
                                <form method="POST" action="{{ route('system.tools.seeders') }}">
                                    @csrf
                                    <input type="hidden" name="seeder" value="TokenSeeder">
                                    <button type="submit" name="ejecutar" class="btn btn-light btn-sm">
                                        <i class="fas fa-key"></i> Crear Tokens
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Seeder Personalizado -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-dark">
                            <div class="card-body text-center">
                                <i class="fas fa-cog fa-2x mb-2"></i>
                                <h6>Seeder Personalizado</h6>
                                <p class="card-text">Ejecuta un seeder específico</p>
                                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#seederModal">
                                    <i class="fas fa-cogs"></i> Personalizado
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resultado de la Ejecución -->
@if($resultado)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-terminal"></i>
                    Resultado de la Ejecución
                    @if($resultado['success'])
                        <span class="badge badge-success">Éxito</span>
                    @else
                        <span class="badge badge-danger">Error</span>
                    @endif
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Comando Ejecutado:</h6>
                        <code>{{ $resultado['comando'] }}</code>

                        @if(!empty($resultado['opciones']))
                        <h6 class="mt-3">Opciones:</h6>
                        <ul>
                            @foreach($resultado['opciones'] as $key => $value)
                            <li><code>{{ $key }}: {{ $value }}</code></li>
                            @endforeach
                        </ul>
                        @endif

                        <h6 class="mt-3">Código de Salida:</h6>
                        <span class="badge badge-{{ $resultado['exit_code'] === 0 ? 'success' : 'danger' }}">
                            {{ $resultado['exit_code'] }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <h6>Salida del Comando:</h6>
                        <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto; font-size: 12px;">{{ $resultado['output'] }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Seeders Disponibles -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Seeders Disponibles</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Clase</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seeders as $clase => $descripcion)
                            <tr>
                                <td><code>{{ $clase }}</code></td>
                                <td>{{ $descripcion }}</td>
                                <td>
                                    <span class="badge badge-success">Disponible</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información Adicional -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información Importante</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-exclamation-triangle"></i> Notas Importantes:</h6>
                    <ul class="mb-0">
                        <li><strong>Seeder Principal:</strong> Ejecuta todos los seeders en el orden correcto.</li>
                        <li><strong>Seeder de Usuarios:</strong> Crea usuarios de prueba con diferentes roles.</li>
                        <li><strong>Seeder de Roles:</strong> Crea roles y permisos del sistema.</li>
                        <li><strong>Seeder de Empresas:</strong> Crea empresas de prueba para las encuestas.</li>
                        <li><strong>Seeder de Tokens:</strong> Crea tokens de prueba para el sistema.</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-shield-alt"></i> Recomendaciones:</h6>
                    <ul class="mb-0">
                        <li>Ejecuta los seeders en el orden correcto: Roles → Usuarios → Empresas.</li>
                        <li>Los seeders pueden sobrescribir datos existentes.</li>
                        <li>Verifica que las migraciones estén ejecutadas antes de los seeders.</li>
                        <li>En producción, usa seeders solo para datos iniciales necesarios.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Seeder Personalizado -->
<div class="modal fade" id="seederModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ejecutar Seeder Personalizado</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('system.tools.seeders') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="seeder">Seleccionar Seeder:</label>
                        <select name="seeder" id="seeder" class="form-control" required>
                            <option value="">Selecciona un seeder...</option>
                            @foreach($seeders as $clase => $descripcion)
                            <option value="{{ $clase }}">{{ $descripcion }} ({{ $clase }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="ejecutar" class="btn btn-primary">
                        <i class="fas fa-play"></i> Ejecutar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.card-body .card {
    border: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.card-body .card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s;
}
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-scroll al resultado si existe
    if ($('.card:contains("Resultado de la Ejecución")').length) {
        $('html, body').animate({
            scrollTop: $('.card:contains("Resultado de la Ejecución")').offset().top - 100
        }, 500);
    }
});
</script>
@endsection
