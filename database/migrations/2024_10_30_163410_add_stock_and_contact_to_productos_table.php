<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('productos', function (Blueprint $table) {
            // Añadimos el campo user_id junto con los otros campos nuevos
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('stock')->default(0);
            $table->string('contacto_whatsapp')->nullable();
        });

        // Ahora que la columna user_id existe, rellenamos los valores donde está en NULL
        DB::table('productos')->whereNull('user_id')->update(['user_id' => 1]); // Cambia 1 por el id de un usuario existente

        // Añadimos la restricción de clave foránea al campo user_id
        Schema::table('productos', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('stock');
            $table->dropColumn('contacto_whatsapp');
        });
    }
};
