<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Orden;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ReconcileEpayco extends Command
{
    protected $signature = 'orders:reconcile-epayco {--days=60} {--dry-run}';
    protected $description = 'Sincroniza estados de órdenes con ePayco (por ref_payco / payload).';

    public function handle()
    {
        $days     = (int)$this->option('days');
        $dryRun   = (bool)$this->option('dry-run');

        $pCustId  = config('services.epayco.p_cust_id_cliente', env('EPAYCO_P_CUST_ID_CLIENTE'));
        $pKey     = config('services.epayco.p_key', env('EPAYCO_P_KEY'));

        if (!$pCustId || !$pKey) {
            $this->error('Falta EPAYCO_P_CUST_ID_CLIENTE o EPAYCO_P_KEY en .env');
            return Command::FAILURE;
        }

        $q = Orden::query()
            ->whereIn('estado', ['pendiente','rechazada','cancelada'])
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('id');

        $total = $q->count();
        $this->info("Órdenes candidatas: {$total}");

        $q->chunkById(50, function($ordenes) use ($dryRun, $pCustId, $pKey) {
            foreach ($ordenes as $orden) {
                $ref = $orden->ref_epayco ?? data_get($orden->payload, 'x_ref_payco');
                $status = null;

                if ($ref) {
                    try {
                        $url = "https://secure.epayco.co/validation/v1/reference/{$ref}";
                        $resp = Http::timeout(20)->get($url)->json();
                        $data = $resp['data'] ?? [];

                        $cod  = (int)($data['x_cod_response'] ?? 0);
                        $txt  = strtolower((string)($data['x_response'] ?? ''));

                        if ($cod === 1 || in_array($txt, ['aprobada','aceptada'], true)) {
                            $status = 'pagada';
                        } elseif (in_array($txt, ['rechazada','fallida'], true)) {
                            $status = 'rechazada';
                        } elseif ($txt === 'cancelada') {
                            $status = 'cancelada';
                        } else {
                            $status = 'pendiente';
                        }
                    } catch (\Throwable $e) {
                        $this->warn("Orden #{$orden->id}: error validando ref {$ref} -> {$e->getMessage()}");
                    }
                }

                if (!$status && $orden->payload) {
                    $estado  = strtolower((string) data_get($orden->payload, 'x_response', ''));
                    $codResp = (int) data_get($orden->payload, 'x_cod_response', 0);

                    if ($codResp === 1 || in_array($estado, ['aprobada','aceptada'], true)) {
                        $status = 'pagada';
                    } elseif (in_array($estado, ['rechazada','fallida'], true)) {
                        $status = 'rechazada';
                    } elseif ($estado === 'cancelada') {
                        $status = 'cancelada';
                    } else {
                        $status = 'pendiente';
                    }
                }

                if (!$status) {
                    $this->line("Orden #{$orden->id}: no se pudo determinar estado (sin ref/payload)");
                    continue;
                }

                if ($status === 'pagada') {
                    if ($orden->estado !== 'pagada') {
                        if ($dryRun) {
                            $this->info("DRY-RUN -> Orden #{$orden->id} => pagada");
                        } else {
                            DB::transaction(function () use ($orden) {
                                $orden->load('detalles');
                                foreach ($orden->detalles as $det) {
                                    $p = \App\Models\Producto::lockForUpdate()->find($det->producto_id);
                                    if ($p) {
                                        $p->stock = max(0, (int)$p->stock - (int)$det->cantidad);
                                        $p->save();
                                    }
                                }
                                $orden->estado = 'pagada';
                                $orden->respuesta = 'Aprobada (reconciliación)';
                                $orden->save();
                            });
                            $this->info("Orden #{$orden->id} => PAGADA");
                        }
                    } else {
                        $this->line("Orden #{$orden->id}: ya estaba pagada");
                    }
                } else {
                    if ($orden->estado !== $status) {
                        if ($dryRun) {
                            $this->info("DRY-RUN -> Orden #{$orden->id} => {$status}");
                        } else {
                            $orden->estado = $status;
                            $orden->respuesta = ucfirst($status)." (reconciliación)";
                            $orden->save();
                            $this->info("Orden #{$orden->id} => {$status}");
                        }
                    } else {
                        $this->line("Orden #{$orden->id}: sin cambios ({$orden->estado})");
                    }
                }
            }
        });

        return Command::SUCCESS;
    }
}
