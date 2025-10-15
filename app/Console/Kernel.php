<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ReconcileEpayco::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // --- Reconciliación de pagos ePayco ---
        // Corre cada 15 min, NO se solapa si una ejecución tarda.
        // Guarda salida en storage/logs/reconcile_epayco.log
        $schedule->command('orders:reconcile-epayco --days=90')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/reconcile_epayco.log'));

        // Si quieres que en local sea más frecuente, descomenta esto:
        // if (app()->environment('local')) {
        //     $schedule->command('orders:reconcile-epayco --days=7')
        //         ->everyFiveMinutes()
        //         ->withoutOverlapping()
        //         ->appendOutputTo(storage_path('logs/reconcile_epayco.log'));
        // }
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
