@extends('adminlte::page')

@section('title', 'Configuración de Envío de Correos')

@section('content_header')
    <h1>
        <i class="fas fa-envelope"></i> Configuración de Envío de Correos
        <small>Asistente de configuración</small>
    </h1>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Wizard Progress -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-magic"></i> Asistente de Configuración
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="progress-group">
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 25%" id="progress-bar">
                                        <span class="progress-text">Paso 1 de 4</span>
                                    </div>
                                </div>
                                <div class="progress-steps mt-2">
                                    <div class="step active" data-step="1">
                                        <i class="fas fa-building"></i>
                                        <span>Seleccionar Empresa</span>
                                    </div>
                                    <div class="step" data-step="2">
                                        <i class="fas fa-list"></i>
                                        <span>Seleccionar Encuestas</span>
                                    </div>
                                    <div class="step" data-step="3">
                                        <i class="fas fa-cog"></i>
                                        <span>Configurar Envío</span>
                                    </div>
                                    <div class="step" data-step="4">
                                        <i class="fas fa-check"></i>
                                        <span>Confirmar</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Selección de Empresa -->
            <div class="card card-outline card-primary" id="step-1">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i> Paso 1: Seleccionar Empresa
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="empresa_id">
                                    <i class="fas fa-building"></i> Empresa
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="empresa_id" name="empresa_id" required>
                                    <option value="">Seleccione una empresa...</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Seleccione la empresa para la cual desea configurar el envío de correos
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-info-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Empresas Disponibles</span>
                                    <span class="info-box-number">{{ $empresas->count() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Total de empresas registradas
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-primary" id="btn-next-step-1" disabled>
                        <i class="fas fa-arrow-right"></i> Continuar
                    </button>
                </div>
            </div>

            <!-- Step 2: Selección de Encuestas (Hidden initially) -->
            <div class="card card-outline card-primary" id="step-2" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Paso 2: Seleccionar Encuestas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="loading-encuestas" class="text-center" style="display: none;">
                                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                <p class="mt-2">Cargando encuestas...</p>
                            </div>

                            <div id="encuestas-container" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Empresa seleccionada:</strong> <span id="empresa-nombre"></span>
                                </div>

                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-check-square"></i> Seleccionar Encuestas
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="select-all">
                                            <i class="fas fa-check-double"></i> Seleccionar Todas
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all">
                                            <i class="fas fa-times"></i> Deseleccionar Todas
                                        </button>
                                    </div>
                                </div>

                                <div id="encuestas-list" class="row">
                                    <!-- Las encuestas se cargarán dinámicamente aquí -->
                                </div>
                            </div>

                            <div id="no-encuestas" class="alert alert-warning" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>No hay encuestas disponibles</strong>
                                <p class="mb-0">La empresa seleccionada no tiene encuestas registradas.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-secondary" id="btn-prev-step-2">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-next-step-2" disabled>
                        <i class="fas fa-arrow-right"></i> Continuar
                    </button>
                </div>
            </div>

            <!-- Step 3: Configuración (Hidden initially) -->
            <div class="card card-outline card-primary" id="step-3" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> Paso 3: Configurar Envío de Correos
                    </h3>
                </div>
                <div class="card-body">
                    <div id="configuracion-container">
                        <!-- La configuración se cargará dinámicamente aquí -->
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-secondary" id="btn-prev-step-3">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-next-step-3" disabled>
                        <i class="fas fa-arrow-right"></i> Continuar
                    </button>
                </div>
            </div>

            <!-- Step 4: Confirmación (Hidden initially) -->
            <div class="card card-outline card-primary" id="step-4" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check"></i> Paso 4: Confirmar Configuración
                    </h3>
                </div>
                <div class="card-body">
                    <div id="resumen-container">
                        <!-- El resumen se cargará dinámicamente aquí -->
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-secondary" id="btn-prev-step-4">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-success" id="btn-save-config">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress-steps {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    flex: 1;
    position: relative;
    color: #6c757d;
}

.step.active {
    color: #007bff;
}

.step.completed {
    color: #28a745;
}

.step i {
    font-size: 24px;
    margin-bottom: 5px;
}

.step span {
    font-size: 12px;
    font-weight: 500;
}

.step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 12px;
    right: -50%;
    width: 100%;
    height: 2px;
    background-color: #e9ecef;
    z-index: -1;
}

.step.completed:not(:last-child)::after {
    background-color: #28a745;
}

.encuesta-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.encuesta-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
}

.encuesta-card.selected {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.encuesta-card .estado-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.configuracion-form {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    background-color: #f8f9fa;
}

.configuracion-form h5 {
    color: #495057;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.resumen-item {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.resumen-item h6 {
    color: #007bff;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
    margin-bottom: 15px;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-content {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    let selectedEmpresa = null;
    let selectedEncuestas = [];
    let configuracionData = {};

    // Elementos del DOM
    const empresaSelect = document.getElementById('empresa_id');
    const btnNextStep1 = document.getElementById('btn-next-step-1');
    const btnNextStep2 = document.getElementById('btn-next-step-2');
    const btnNextStep3 = document.getElementById('btn-next-step-3');
    const btnPrevStep2 = document.getElementById('btn-prev-step-2');
    const btnPrevStep3 = document.getElementById('btn-prev-step-3');
    const btnPrevStep4 = document.getElementById('btn-prev-step-4');
    const btnSaveConfig = document.getElementById('btn-save-config');

    // Event listeners
    empresaSelect.addEventListener('change', handleEmpresaChange);
    btnNextStep1.addEventListener('click', () => nextStep(2));
    btnNextStep2.addEventListener('click', () => nextStep(3));
    btnNextStep3.addEventListener('click', () => nextStep(4));
    btnPrevStep2.addEventListener('click', () => prevStep(1));
    btnPrevStep3.addEventListener('click', () => prevStep(2));
    btnPrevStep4.addEventListener('click', () => prevStep(3));
    btnSaveConfig.addEventListener('click', saveConfiguracion);

    // Funciones
    function handleEmpresaChange() {
        const empresaId = empresaSelect.value;
        btnNextStep1.disabled = !empresaId;

        if (empresaId) {
            selectedEmpresa = empresaId;
        }
    }

    function nextStep(step) {
        if (step === 2) {
            loadEncuestas();
        } else if (step === 3) {
            loadConfiguracion();
        } else if (step === 4) {
            loadResumen();
        }

        showStep(step);
        updateProgress(step);
    }

    function prevStep(step) {
        showStep(step);
        updateProgress(step);
    }

    function showStep(step) {
        // Ocultar todos los pasos
        for (let i = 1; i <= 4; i++) {
            document.getElementById(`step-${i}`).style.display = 'none';
        }

        // Mostrar el paso actual
        document.getElementById(`step-${step}`).style.display = 'block';
        currentStep = step;
    }

    function updateProgress(step) {
        const progressBar = document.getElementById('progress-bar');
        const progressText = progressBar.querySelector('.progress-text');
        const percentage = (step / 4) * 100;

        progressBar.style.width = percentage + '%';
        progressText.textContent = `Paso ${step} de 4`;

        // Actualizar indicadores de pasos
        document.querySelectorAll('.step').forEach((stepEl, index) => {
            stepEl.classList.remove('active', 'completed');
            if (index + 1 < step) {
                stepEl.classList.add('completed');
            } else if (index + 1 === step) {
                stepEl.classList.add('active');
            }
        });
    }

    function loadEncuestas() {
        const loadingDiv = document.getElementById('loading-encuestas');
        const containerDiv = document.getElementById('encuestas-container');
        const noEncuestasDiv = document.getElementById('no-encuestas');

        loadingDiv.style.display = 'block';
        containerDiv.style.display = 'none';
        noEncuestasDiv.style.display = 'none';

        fetch('{{ route("configuracion-envio.get-encuestas") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                empresa_id: selectedEmpresa
            })
        })
        .then(response => response.json())
        .then(data => {
            loadingDiv.style.display = 'none';

            if (data.success) {
                if (data.data.encuestas.length > 0) {
                    renderEncuestas(data.data.encuestas, data.data.empresa);
                    containerDiv.style.display = 'block';
                } else {
                    noEncuestasDiv.style.display = 'block';
                }
            } else {
                showError('Error al cargar encuestas: ' + data.message);
            }
        })
        .catch(error => {
            loadingDiv.style.display = 'none';
            showError('Error de conexión: ' + error.message);
        });
    }

    function renderEncuestas(encuestas, empresa) {
        document.getElementById('empresa-nombre').textContent = empresa.nombre;

        const container = document.getElementById('encuestas-list');
        container.innerHTML = '';

        encuestas.forEach(encuesta => {
            const card = document.createElement('div');
            card.className = 'col-md-6 col-lg-4';
            card.innerHTML = `
                <div class="encuesta-card" data-encuesta-id="${encuesta.id}">
                    <div class="estado-badge">
                        <span class="badge badge-${encuesta.configurado ? 'success' : 'warning'}">
                            ${encuesta.estado_configuracion}
                        </span>
                    </div>
                    <h6 class="mb-2">${encuesta.titulo}</h6>
                    <p class="text-muted mb-2">${encuesta.descripcion || 'Sin descripción'}</p>
                    <small class="text-muted">
                        <i class="fas fa-calendar"></i> Creada: ${new Date(encuesta.created_at).toLocaleDateString()}
                    </small>
                </div>
            `;

            card.querySelector('.encuesta-card').addEventListener('click', function() {
                toggleEncuestaSelection(encuesta.id, this);
            });

            container.appendChild(card);
        });

        // Event listeners para botones de selección
        document.getElementById('select-all').addEventListener('click', selectAllEncuestas);
        document.getElementById('deselect-all').addEventListener('click', deselectAllEncuestas);
    }

    function toggleEncuestaSelection(encuestaId, element) {
        const index = selectedEncuestas.indexOf(encuestaId);

        if (index > -1) {
            selectedEncuestas.splice(index, 1);
            element.classList.remove('selected');
        } else {
            selectedEncuestas.push(encuestaId);
            element.classList.add('selected');
        }

        btnNextStep2.disabled = selectedEncuestas.length === 0;
    }

    function selectAllEncuestas() {
        document.querySelectorAll('.encuesta-card').forEach(card => {
            const encuestaId = parseInt(card.dataset.encuestaId);
            if (!selectedEncuestas.includes(encuestaId)) {
                selectedEncuestas.push(encuestaId);
                card.classList.add('selected');
            }
        });
        btnNextStep2.disabled = false;
    }

    function deselectAllEncuestas() {
        selectedEncuestas = [];
        document.querySelectorAll('.encuesta-card').forEach(card => {
            card.classList.remove('selected');
        });
        btnNextStep2.disabled = true;
    }

    function loadConfiguracion() {
        // Esta función se implementará en el siguiente paso
        btnNextStep3.disabled = false;
    }

    function loadResumen() {
        // Esta función se implementará en el siguiente paso
    }

    function saveConfiguracion() {
        // Esta función se implementará en el siguiente paso
    }

    function showError(message) {
        // Mostrar error usando SweetAlert o similar
        alert(message);
    }

    // Inicialización
    updateProgress(1);
});
</script>
@endsection
