<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ordenes', function (Blueprint $t) {
        $t->id();
        $t->foreignId('user_id')->constrained()->cascadeOnDelete(); // users.id
        $t->string('estado')->default('pendiente'); // pendiente|pagada|rechazada|fallida
        $t->decimal('subtotal', 12, 2);
        $t->decimal('envio', 12, 2)->default(0);
        $t->decimal('total', 12, 2);

        // Referencias ePayco (para conciliación / auditoría)
        $t->string('ref_epayco')->nullable();      // x_ref_payco
        $t->string('trx_id')->nullable();          // x_transaction_id
        $t->string('respuesta')->nullable();       // x_response (Aceptada/Rechazada/…)
        $t->json('payload')->nullable();           // crudo del webhook

        $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('ordenes');
    }
};
