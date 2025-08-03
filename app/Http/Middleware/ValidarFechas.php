<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\FechaHelper;

class ValidarFechas
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Validar fechas en el request
        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            try {
                $fechaInicio = Carbon::parse($request->fecha_inicio);
                $hoy = Carbon::now()->startOfDay();

                if ($fechaInicio->lt($hoy)) {
                    return back()->withErrors([
                        'fecha_inicio' => 'La fecha de inicio debe ser igual o posterior a hoy (' . $hoy->format('d/m/Y') . ').'
                    ])->withInput();
                }
            } catch (\Exception $e) {
                return back()->withErrors([
                    'fecha_inicio' => 'La fecha de inicio no tiene un formato válido.'
                ])->withInput();
            }
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            try {
                $fechaFin = Carbon::parse($request->fecha_fin);
                $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : null;

                if ($fechaInicio && $fechaFin->lt($fechaInicio)) {
                    return back()->withErrors([
                        'fecha_fin' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.'
                    ])->withInput();
                }
            } catch (\Exception $e) {
                return back()->withErrors([
                    'fecha_fin' => 'La fecha de fin no tiene un formato válido.'
                ])->withInput();
            }
        }

        return $next($request);
    }
}
