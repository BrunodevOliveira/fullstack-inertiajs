<?php

namespace Database\Seeders;

use App\Enums\PerfilEnum;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = [
            [
                'cpf' => '00000000000',
                'nome' => 'Usuário Root',
                'email' => 'root@pinc.edu.br',
                'telefone' => '(99) 99999-0000',
                'perfis' => [PerfilEnum::Root->value],
            ],
            [
                'cpf' => '11111111111',
                'nome' => 'Coordenação / Administrador',
                'email' => 'admin@pinc.edu.br',
                'telefone' => '(99) 99999-1111',
                'perfis' => [PerfilEnum::Administrador->value],
            ],
            [
                'cpf' => '22222222222',
                'nome' => 'Prof. Dr. Docente Exemplo',
                'email' => 'docente@pinc.edu.br',
                'telefone' => '(99) 99999-2222',
                'siape' => '1234567',
                'lattes' => 'http://lattes.cnpq.br/1234567890123456',
                'perfis' => [PerfilEnum::Docente->value],
            ],
            [
                'cpf' => '33333333333',
                'nome' => 'Discente Aluno Exemplo',
                'email' => 'aluno@pinc.edu.br',
                'telefone' => '(99) 99999-3333',
                'sira' => '20261001',
                'curso_importado' => 'Sistemas de Informação',
                'perfis' => [PerfilEnum::Discente->value],
            ],
            [
                'cpf' => '44444444444',
                'nome' => 'Técnico Administrativo Exemplo',
                'email' => 'tecnico@pinc.edu.br',
                'telefone' => '(99) 99999-4444',
                'siape' => '7654321',
                'perfis' => [PerfilEnum::Tecnico->value],
            ],
            [
                'cpf' => '55555555555',
                'nome' => 'Prof. Coordenador (Docente + Admin)',
                'email' => 'docente.admin@pinc.edu.br',
                'telefone' => '(99) 99999-5555',
                'siape' => '9876543',
                'lattes' => 'http://lattes.cnpq.br/9876543210987654',
                'perfis' => [PerfilEnum::Docente->value, PerfilEnum::Administrador->value],
            ],
        ];

        foreach ($usuarios as $dados) {
            $perfis = $dados['perfis'];
            $cursoImportado = $dados['curso_importado'] ?? null;
            /**
             *  unset()->Função nativa do PHP que remove variáveis ou chaves de um array.
             *  Quardamos o perfil e o curso nas variáveis acima. Fazemos isso pois os campos
             *  'perfis' e 'curso_importado' não existem na tabela 'usuarios', se tentar criar
             * um usuário enviando esses campos daria erro.
             */
            unset($dados['perfis'], $dados['curso_importado']);

            // Senha padrão 'password' para todos
            $dados['password'] = 'password';
            $dados['situacao'] = true;
            $usuario = Usuario::updateOrCreate(
                ['cpf' => $dados['cpf']], // Critério de busca
                $dados // Dados que serão criados/atualizados
            );

            // Sincroniza os perfis na tabela pivô
            $usuario->perfis()->sync($perfis);

            // Se for discente com curso, cria ou atualiza o registro na tabela alunos
            if ($cursoImportado) {
                $usuario->aluno()->updateOrCreate(
                    ['usuario_id' => $usuario->id],
                    ['curso_importado' => $cursoImportado]
                );
            }
        }
    }
}
