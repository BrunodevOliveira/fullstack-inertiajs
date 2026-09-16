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
        Schema::create('usuarios', function (Blueprint $table) {

            // Identificadores e Dados Básicos
            $table->id();
            $table->string('cpf', 11)->unique();
            $table->string('nome');
            $table->string('nome_social')->nullable();
            $table->string('email')->unique();

            // Dados Complementares (Acadêmicos e Contato):
            $table->string('telefone', 20)->nullable();
            $table->string('lattes')->nullable();
            $table->string('siape', 20)->nullable();
            $table->string('sira', 20)->nullable();

            // Status e Segurança (Autenticação do Laravel)
            $table->boolean('situacao')->default(true);
            $table->string('password');
            $table->rememberToken(); // Helper do Laravel que cria a coluna remember_token (VARCHAR 100 nullable) para a função "Lembrar de mim".

            // Auditoria e Histórico
            $table->timestamps();
            $table->softDeletes(); // Cria a coluna deleted_at nullable para exclusão lógica (sem perder histórico de projetos)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
