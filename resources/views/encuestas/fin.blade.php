@extends('layouts.encuesta-publica')

@section('content')
    @if(isset($encuesta) && $encuesta)
        <div class="encuesta-header">
            <h1 class="encuesta-title">{{ $encuesta->titulo }}</h1>
            @if($encuesta->empresa)
                <p class="encuesta-subtitle">{{ $encuesta->empresa->nombre }}</p>
            @endif
        </div>

        <div class="fin-encuesta-container">
            <div class="fin-encuesta-card">
                <div class="fin-encuesta-icon">
                    <i class="fas fa-check-circle"></i>
                </div>

                <h2 class="fin-encuesta-title">¡Gracias por responder la encuesta!</h2>

                <p class="fin-encuesta-message">
                    Tu respuesta ha sido registrada exitosamente.
                    Agradecemos tu tiempo y participación.
                </p>

                <div class="fin-encuesta-info">
                    <p><strong>Encuesta:</strong> {{ $encuesta->titulo }}</p>
                    @if($encuesta->empresa)
                        <p><strong>Empresa:</strong> {{ $encuesta->empresa->nombre }}</p>
                    @endif
                    <p><strong>Fecha:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                </div>

                                <div class="fin-encuesta-message-close">
                    <p class="fin-encuesta-close-text">
                        <i class="fas fa-check-circle"></i>
                        Ya puedes cerrar esta ventana
                    </p>
                </div>

                <div class="fin-encuesta-timer">
                    <p class="text-muted">
                        <i class="fas fa-clock"></i>
                        Esta ventana se cerrará automáticamente en <span id="countdown">180</span> segundos
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="text-center">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h3>Encuesta no disponible</h3>
            <p class="text-muted">
                La encuesta que buscas no está disponible o ha expirado.
            </p>
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Volver al inicio
            </a>
        </div>
    @endif

    <style>
        .fin-encuesta-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60vh;
            padding: 2rem;
        }

        .fin-encuesta-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .fin-encuesta-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1.5rem;
        }

        .fin-encuesta-title {
            color: #333;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .fin-encuesta-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .fin-encuesta-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .fin-encuesta-info p {
            margin-bottom: 0.5rem;
            color: #555;
        }

        .fin-encuesta-info p:last-child {
            margin-bottom: 0;
        }

        .fin-encuesta-message-close {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .fin-encuesta-close-text {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .fin-encuesta-close-text i {
            font-size: 1.5rem;
        }

        .fin-encuesta-timer {
            border-top: 1px solid #eee;
            padding-top: 1.5rem;
        }

        .fin-encuesta-timer p {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        #countdown {
            font-weight: bold;
            color: #007bff;
        }

        @media (max-width: 768px) {
            .fin-encuesta-card {
                padding: 2rem;
                margin: 1rem;
            }

            .fin-encuesta-title {
                font-size: 1.5rem;
            }

            .fin-encuesta-close-text {
                font-size: 1rem;
                flex-direction: column;
                gap: 0.25rem;
            }

            .fin-encuesta-close-text i {
                font-size: 1.2rem;
            }
        }
    </style>

    <script>
        // Auto-cierre de la ventana después de 3 minutos (180 segundos)
        let countdown = 180;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                window.close();
            }
        }, 1000);

                // Auto-cierre simplificado - solo cerrar después de 3 minutos
        // No hay interacción del usuario, solo esperar y cerrar
    </script>
@endsection
