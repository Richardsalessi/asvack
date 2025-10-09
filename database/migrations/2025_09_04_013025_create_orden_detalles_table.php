<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orden_detalles', function (Blueprint $t) {
        $t->id();
        $t->foreignId('orden_id')->constrained('ordenes')->cascadeOnDelete();
        $t->foreignId('producto_id')->constrained('productos')->restrictOnDelete(); // preserva histórico
        $t->integer('cantidad');
        $t->decimal('precio_unitario', 12, 2);
        $t->decimal('subtotal', 12, 2);
        $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('orden_detalles');
    }
};

