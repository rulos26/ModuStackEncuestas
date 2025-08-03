<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\FechaHelper;
use Carbon\Carbon;

class SystemController extends Controller
{
    public function info()
    {
        $info = [
            'fechas' => FechaHelper::getInfoZonaHoraria(),
            'configuracion' => [
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'debug' => config('app.debug'),
                'env' => config('app.env'),
            ],
            'validaciones' => [
                'hoy' => [
                    'fecha' => Carbon::now()->format('Y-m-d'),
                    'valida' => FechaHelper::esFechaInicioValida(Carbon::now()->format('Y-m-d')),
                    'mensaje' => FechaHelper::esFechaInicioValida(Carbon::now()->format('Y-m-d')) ? '✅ Válida' : '❌ Inválida'
                ],
                'ayer' => [
                    'fecha' => Carbon::now()->subDay()->format('Y-m-d'),
                    'valida' => FechaHelper::esFechaInicioValida(Carbon::now()->subDay()->format('Y-m-d')),
                    'mensaje' => FechaHelper::esFechaInicioValida(Carbon::now()->subDay()->format('Y-m-d')) ? '✅ Válida' : '❌ Inválida'
                ],
                'manana' => [
                    'fecha' => Carbon::now()->addDay()->format('Y-m-d'),
                    'valida' => FechaHelper::esFechaInicioValida(Carbon::now()->addDay()->format('Y-m-d')),
                    'mensaje' => FechaHelper::esFechaInicioValida(Carbon::now()->addDay()->format('Y-m-d')) ? '✅ Válida' : '❌ Inválida'
                ]
            ]
        ];

        return view('system.info', compact('info'));
    }
}
