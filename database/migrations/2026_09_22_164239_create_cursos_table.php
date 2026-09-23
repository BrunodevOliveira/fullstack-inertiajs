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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            //restrictOnDelete-> Não permita excluir o registro pai(CAmpos) enquanto existirem registros filhos relacionados(cursos)
            $table->foreignId('campus_id')->constrained('campuses')->restrictOnDelete();
            // nullOnDelete-> Ao excluir o pai, coloca NULL na FK dos filhos. Por isso que usamos nullable()
            $table->foreignId('disciplina_id')->nullable()->constrained('disciplinas')->nullOnDelete();
            $table->unsignedInteger('documento_id')->nullable();
            $table->string('email')->nullable();
            $table->boolean('colaborador')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
