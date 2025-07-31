<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $encuesta->titulo ?? 'Encuesta' }} - ModuStack</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .encuesta-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 2rem auto;
            max-width: 800px;
            padding: 2rem;
        }

        .encuesta-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }

        .encuesta-title {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .encuesta-subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .pregunta-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }

        .pregunta-texto {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .respuesta-option {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .respuesta-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .respuesta-option input[type="radio"]:checked + label,
        .respuesta-option input[type="checkbox"]:checked + label,
        .escala-option input[type="radio"]:checked + label {
            color: #667eea;
            font-weight: 600;
        }

        .btn-enviar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-enviar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            color: #c33;
        }

        .success-message {
            background: #efe;
            border: 1px solid #cfc;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            color: #3c3;
        }

        .obligatoria {
            color: #e74c3c;
            font-weight: bold;
        }

        .texto-libre {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
        }

        .texto-libre:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .escala-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .escala-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .escala-label {
            font-size: 0.9rem;
            text-align: center;
            max-width: 80px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="encuesta-container">
            @if(isset($error))
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ $error }}
                </div>
            @endif

            @if(session('error'))
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Mejorar UX de las opciones
        document.addEventListener('DOMContentLoaded', function() {
            // Marcar opciones seleccionadas
            const radioButtons = document.querySelectorAll('input[type="radio"]');
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');

            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remover selección previa en el mismo grupo
                    const name = this.name;
                    document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
                        const parent = r.closest('.respuesta-option') || r.closest('.escala-option');
                        if (parent) {
                            parent.classList.remove('selected');
                        }
                    });

                    // Marcar como seleccionada
                    if (this.checked) {
                        const parent = this.closest('.respuesta-option') || this.closest('.escala-option');
                        if (parent) {
                            parent.classList.add('selected');
                        }
                    }
                });
            });

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const parent = this.closest('.respuesta-option') || this.closest('.escala-option');
                    if (parent) {
                        if (this.checked) {
                            parent.classList.add('selected');
                        } else {
                            parent.classList.remove('selected');
                        }
                    }
                });
            });

            // Validación de formulario
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const obligatorias = document.querySelectorAll('.obligatoria');
                    let faltantes = [];

                    obligatorias.forEach(obligatoria => {
                        const preguntaId = obligatoria.dataset.preguntaId;
                        const preguntaTexto = obligatoria.dataset.preguntaTexto;

                        // Buscar diferentes tipos de respuestas
                        let respuesta = document.querySelector(`input[name="respuestas[${preguntaId}]"]`);
                        let textarea = document.querySelector(`textarea[name="respuestas[${preguntaId}]"]`);
                        let select = document.querySelector(`select[name="respuestas[${preguntaId}]"]`);
                        let checkboxes = document.querySelectorAll(`input[name="respuestas[${preguntaId}][]"]`);

                        let tieneRespuesta = false;

                        // Verificar radio buttons
                        if (respuesta && respuesta.type === 'radio') {
                            const radios = document.querySelectorAll(`input[name="respuestas[${preguntaId}]"]`);
                            radios.forEach(radio => {
                                if (radio.checked) {
                                    tieneRespuesta = true;
                                }
                            });
                        }
                        // Verificar checkbox (buscar específicamente checkboxes con corchetes)
                        else if (checkboxes.length > 0) {
                            checkboxes.forEach(checkbox => {
                                if (checkbox.checked) {
                                    tieneRespuesta = true;
                                }
                            });
                        }
                        // Verificar texto, textarea, select
                        else if ((respuesta && respuesta.value.trim()) ||
                                 (textarea && textarea.value.trim()) ||
                                 (select && select.value.trim())) {
                            tieneRespuesta = true;
                        }

                        if (!tieneRespuesta) {
                            faltantes.push(preguntaTexto || `Pregunta ${preguntaId}`);
                        }
                    });

                    if (faltantes.length > 0) {
                        e.preventDefault();
                        alert('Por favor, responde todas las preguntas obligatorias:\n' + faltantes.join('\n'));
                    }
                });
            }
        });
    </script>
</body>
</html>
