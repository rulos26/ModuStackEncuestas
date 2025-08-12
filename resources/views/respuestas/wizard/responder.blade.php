@extends('adminlte::page')

@section('title', 'Configurar Respuestas - Pregunta ' . ($preguntaIndex + 1))



@section('content')
<div class="container">
    <h3>Agregar Respuestas</h3>

    <form action="{{ route('respuestas.store') }}" method="POST">
        @csrf

        <!-- Contenedor de respuestas -->
        <div id="respuestasContainer">
            <div class="row mb-2 respuesta-item">
                <div class="col-md-8">
                    <input type="text" id="respuesta-texto-0" name="respuestas[0][texto]" class="form-control"
                           placeholder="Escribe la opción de respuesta..." required>
                </div>
                <div class="col-md-2">
                    <input type="number" id="respuesta-orden-0" name="respuestas[0][orden]" class="form-control"
                           placeholder="Orden" value="1" min="1" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-block btn-remove-respuesta">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>

        <!-- Botón agregar -->
        <div class="mb-3">
            <button type="button" id="btnAgregarRespuesta" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar Respuesta
            </button>
        </div>

        <!-- Contador -->
        <p>Total de respuestas: <span id="numRespuestas">1</span></p>

        <!-- Botón enviar -->
        <div class="mt-3">
            <button type="submit" class="btn btn-success">Guardar Respuestas</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    console.log("jQuery listo y DOM cargado.");

    // Contador para indexar respuestas
    let index = $('#respuestasContainer .respuesta-item').length;

    // Agregar nueva respuesta
    $('#btnAgregarRespuesta').on('click', function (e) {
        e.preventDefault();

        let nuevaRespuesta = `
            <div class="row mb-2 respuesta-item">
                <div class="col-md-8">
                    <input type="text" id="respuesta-texto-${index}" name="respuestas[${index}][texto]" class="form-control"
                           placeholder="Escribe la opción de respuesta..." required>
                </div>
                <div class="col-md-2">
                    <input type="number" id="respuesta-orden-${index}" name="respuestas[${index}][orden]" class="form-control"
                           placeholder="Orden" value="${index + 1}" min="1" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-block btn-remove-respuesta">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        `;

        $('#respuestasContainer').append(nuevaRespuesta);
        index++;
        actualizarContador();
    });

    // Eliminar respuesta
    $(document).on('click', '.btn-remove-respuesta', function () {
        $(this).closest('.respuesta-item').remove();
        actualizarContador();
    });

    // Actualizar contador visual
    function actualizarContador() {
        let total = $('#respuestasContainer .respuesta-item').length;
        $('#numRespuestas').text(total);
    }
});
</script>
@endsection
