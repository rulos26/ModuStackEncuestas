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
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --text-color: #2c3e50;
            --muted-color: #7f8c8d;
            --border-color: #e9ecef;
            --background-color: #f8f9fa;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .encuesta-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 2rem auto;
            max-width: 800px;
            padding: 2rem;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .encuesta-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--background-color);
        }

        .encuesta-title {
            color: var(--text-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .encuesta-subtitle {
            color: var(--muted-color);
            font-size: 1.1rem;
        }

        .pregunta-card {
            background: var(--background-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .pregunta-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .pregunta-texto {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .respuesta-option {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .respuesta-option:hover {
            border-color: var(--primary-color);
            background: #f8f9ff;
            transform: translateX(5px);
        }

        .respuesta-option.selected {
            border-color: var(--primary-color);
            background: #f0f4ff;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }

        .respuesta-option input[type="radio"],
        .respuesta-option input[type="checkbox"] {
            position: absolute;
            opacity: 0;
        }

        .respuesta-option label {
            cursor: pointer;
            margin: 0;
            width: 100%;
            display: block;
        }

        .btn-enviar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-enviar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-enviar:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .obligatoria {
            color: var(--danger-color);
            font-weight: bold;
        }

        .texto-libre {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .texto-libre:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .escala-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .escala-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.2s ease;
        }

        .escala-option:hover {
            transform: scale(1.05);
        }

        .escala-label {
            font-size: 0.9rem;
            text-align: center;
            max-width: 80px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .encuesta-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .encuesta-title {
                font-size: 1.5rem;
            }

            .pregunta-card {
                padding: 1rem;
            }

            .escala-container {
                gap: 0.5rem;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="encuesta-container">
            @if(isset($error))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ $error }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Mejorar UX de las opciones
            const radioButtons = document.querySelectorAll('input[type="radio"]');
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');

            // Función para manejar selección de radio buttons
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    const name = this.name;
                    // Remover selección previa
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

            // Función para manejar selección de checkboxes
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

            // Validación mejorada del formulario
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;

                    // Mostrar loading
                    submitBtn.innerHTML = '<span class="loading"></span> Enviando...';
                    submitBtn.disabled = true;

                    const obligatorias = document.querySelectorAll('.obligatoria');
                    let faltantes = [];

                    obligatorias.forEach(obligatoria => {
                        const preguntaId = obligatoria.dataset.preguntaId;
                        const preguntaTexto = obligatoria.dataset.preguntaTexto;

                        let tieneRespuesta = false;

                        // Verificar diferentes tipos de respuestas
                        const radioButtons = document.querySelectorAll(`input[name="respuestas[${preguntaId}]"]`);
                        const checkboxes = document.querySelectorAll(`input[name="respuestas[${preguntaId}][]"]`);
                        const textInput = document.querySelector(`input[name="respuestas[${preguntaId}]"]`);
                        const textarea = document.querySelector(`textarea[name="respuestas[${preguntaId}]"]`);
                        const select = document.querySelector(`select[name="respuestas[${preguntaId}]"]`);

                        // Verificar radio buttons
                        radioButtons.forEach(radio => {
                            if (radio.checked) {
                                tieneRespuesta = true;
                            }
                        });

                        // Verificar checkboxes
                        checkboxes.forEach(checkbox => {
                            if (checkbox.checked) {
                                tieneRespuesta = true;
                            }
                        });

                        // Verificar texto, textarea, select
                        if ((textInput && textInput.value.trim()) ||
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

                        // Restaurar botón
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                });
            }

            // Animaciones suaves para las preguntas
            const preguntaCards = document.querySelectorAll('.pregunta-card');
            preguntaCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.animation = 'fadeInUp 0.6s ease-out forwards';
                card.style.opacity = '0';
            });
        });
    </script>
</body>
</html>
