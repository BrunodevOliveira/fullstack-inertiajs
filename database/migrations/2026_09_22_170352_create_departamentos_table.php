<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            //restrictOnDelete-> Não permita excluir o registro pai(CAmpos) enquanto existirem registros filhos relacionados(cursos)
            $table->foreignId('curso_id')->constrained('cursos')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['curso_id', 'nome']);//cria um índice
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
