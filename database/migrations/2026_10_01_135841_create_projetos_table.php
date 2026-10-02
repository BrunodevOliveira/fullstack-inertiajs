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
        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 300);
            $table->string('assunto', 500);
            $table->text('descricao');
            $table->unsignedInteger('vagas')->default(0);
            $table->boolean('arquivado')->nullable()->default(false);
            $table->foreignId('departamento_id')->constrained('departamentos')->restrictOnDelete();
            $table->foreignId('responsavel_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('agencia_id')->nullable()->constrained('agencias')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projetos');
    }
};
