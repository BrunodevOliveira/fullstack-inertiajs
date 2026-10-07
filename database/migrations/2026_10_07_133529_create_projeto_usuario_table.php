<?php

use App\Enums\ParticipanteStatusEnum;
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
        Schema::create('projeto_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->cascadeOnDelete(); // Integridade referencial: se o projeto for excluido, o vínculo some. como projeot e usuario utilizam sofdeletes, esse cascade só será disparado em casos de Hard Delete
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->smallInteger('flags')->default(ParticipanteStatusEnum::Ativo->value);
            $table->timestamps();
            $table->unique(['projeto_id', 'usuario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projeto_usuario');
    }
};
