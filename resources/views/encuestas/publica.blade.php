@extends('layouts.encuesta-publica')

@section('content')
    @if(isset($encuesta) && $encuesta)
        <div class="encuesta-header">
            <h1 class="encuesta-title">{{ $encuesta->titulo }}</h1>
            @if($encuesta->empresa)
                <p class="encuesta-subtitle">{{ $encuesta->empresa->nombre }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('encuestas.responder', $encuesta->id) }}" id="encuesta-form">
            @csrf

            @foreach($encuesta->preguntas as $pregunta)
                <div class="pregunta-card">
                    <div class="pregunta-texto">
                        {{ $loop->iteration }}. {{ $pregunta->texto }}
                        @if($pregunta->obligatoria)
                            <span class="obligatoria" data-pregunta-id="{{ $pregunta->id }}"> *</span>
                        @endif
                    </div>

                    @switch($pregunta->tipo)
                        @case('respuesta_corta')
                            <input type="text"
                                   name="respuestas[{{ $pregunta->id }}]"
                                   class="texto-libre"
                                   placeholder="Escribe tu respuesta aquí..."
                                   @if($pregunta->obligatoria) required @endif>
                            @break

                        @case('parrafo')
                            <textarea name="respuestas[{{ $pregunta->id }}]"
                                      class="texto-libre"
                                      rows="4"
                                      placeholder="Escribe tu respuesta detallada aquí..."
                                      @if($pregunta->obligatoria) required @endif></textarea>
                            @break

                        @case('fecha')
                            <input type="date"
                                   name="respuestas[{{ $pregunta->id }}]"
                                   class="texto-libre"
                                   @if($pregunta->obligatoria) required @endif>
                            @break

                        @case('hora')
                            <input type="time"
                                   name="respuestas[{{ $pregunta->id }}]"
                                   class="texto-libre"
                                   @if($pregunta->obligatoria) required @endif>
                            @break

                        @case('seleccion_unica')
                            @foreach($pregunta->respuestas as $respuesta)
                                <div class="respuesta-option">
                                    <input type="radio"
                                           id="resp_{{ $pregunta->id }}_{{ $respuesta->id }}"
                                           name="respuestas[{{ $pregunta->id }}]"
                                           value="{{ $respuesta->id }}"
                                           @if($pregunta->obligatoria) required @endif>
                                    <label for="resp_{{ $pregunta->id }}_{{ $respuesta->id }}">
                                        {{ $respuesta->texto }}
                                    </label>
                                </div>
                            @endforeach
                            @break

                        @case('casillas_verificacion')
                            @foreach($pregunta->respuestas as $respuesta)
                                <div class="respuesta-option">
                                    <input type="checkbox"
                                           id="resp_{{ $pregunta->id }}_{{ $respuesta->id }}"
                                           name="respuestas[{{ $pregunta->id }}][]"
                                           value="{{ $respuesta->id }}">
                                    <label for="resp_{{ $pregunta->id }}_{{ $respuesta->id }}">
                                        {{ $respuesta->texto }}
                                    </label>
                                </div>
                            @endforeach
                            @break

                        @case('lista_desplegable')
                            <select name="respuestas[{{ $pregunta->id }}]"
                                    class="texto-libre"
                                    @if($pregunta->obligatoria) required @endif>
                                <option value="">Selecciona una opción...</option>
                                @foreach($pregunta->respuestas as $respuesta)
                                    <option value="{{ $respuesta->id }}">{{ $respuesta->texto }}</option>
                                @endforeach
                            </select>
                            @break

                        @case('escala_lineal')
                            <div class="escala-container">
                                @for($i = 1; $i <= $pregunta->escala_max; $i++)
                                    <div class="escala-option">
                                        <input type="radio"
                                               id="resp_{{ $pregunta->id }}_{{ $i }}"
                                               name="respuestas[{{ $pregunta->id }}]"
                                               value="{{ $i }}"
                                               @if($pregunta->obligatoria) required @endif>
                                        <label for="resp_{{ $pregunta->id }}_{{ $i }}" class="escala-label">
                                            {{ $i }}
                                        </label>
                                    </div>
                                @endfor
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Escala del 1 al {{ $pregunta->escala_max }}
                                </small>
                            </div>
                            @break

                        @default
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Tipo de pregunta no soportado: {{ $pregunta->tipo }}
                            </div>
                    @endswitch
                </div>
            @endforeach

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-enviar">
                    <i class="fas fa-paper-plane"></i>
                    Enviar respuestas
                </button>
            </div>
        </form>
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
@endsection
