<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID del cliente
            $table->foreignId('producto_id')->constrained()->onDelete('cascade'); // ID del producto
            $table->integer('cantidad'); // Cantidad comprada
            $table->decimal('precio_total', 10, 2); // Precio total de la compra
            $table->string('telefono')->nullable(); // Teléfono del cliente
            $table->string('ciudad')->nullable(); // Ciudad del cliente
            $table->string('barrio')->nullable(); // Barrio del cliente
            $table->string('direccion')->nullable(); // Dirección del cliente
            $table->timestamps(); // Timestamps para la fecha de la compra
        });
    }

    public function down(): void {
        Schema::dropIfExists('compras');
    }
};
