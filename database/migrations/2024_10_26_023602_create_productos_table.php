    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateProductosTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('productos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->text('descripcion');
                $table->decimal('precio', 10, 2);
                $table->string('imagen')->nullable();
                $table->unsignedBigInteger('categoria_id'); // Relación con categorías
                $table->timestamps();

                // Definición de la clave foránea
                $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('productos');
        }
    }
        