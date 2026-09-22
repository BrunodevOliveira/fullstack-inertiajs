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
            // Usuários Padrão de Demonstração (Dev Switcher)
            [
                'cpf' => '00000000000',
                'nome' => 'Usuário Root',
                'email' => 'root@pinc.edu.br',
                'telefone' => '(99) 99999-0000',
                'perfis' => [PerfilEnum::Root->value],
                'situacao' => true,
            ],
            [
                'cpf' => '11111111111',
                'nome' => 'Coordenação / Administrador',
                'email' => 'admin@pinc.edu.br',
                'telefone' => '(99) 99999-1111',
                'perfis' => [PerfilEnum::Administrador->value],
                'situacao' => true,
            ],
            [
                'cpf' => '22222222222',
                'nome' => 'Prof. Dr. Docente Exemplo',
                'email' => 'docente@pinc.edu.br',
                'telefone' => '(99) 99999-2222',
                'siape' => '1234567',
                'lattes' => 'http://lattes.cnpq.br/1234567890123456',
                'perfis' => [PerfilEnum::Docente->value],
                'situacao' => true,
            ],
            [
                'cpf' => '33333333333',
                'nome' => 'Discente Aluno Exemplo',
                'email' => 'aluno@pinc.edu.br',
                'telefone' => '(99) 99999-3333',
                'sira' => '20261001',
                'curso_importado' => 'Sistemas de Informação',
                'perfis' => [PerfilEnum::Discente->value],
                'situacao' => true,
            ],
            [
                'cpf' => '44444444444',
                'nome' => 'Técnico Administrativo Exemplo',
                'email' => 'tecnico@pinc.edu.br',
                'telefone' => '(99) 99999-4444',
                'siape' => '7654321',
                'perfis' => [PerfilEnum::Tecnico->value],
                'situacao' => true,
            ],
            [
                'cpf' => '55555555555',
                'nome' => 'Prof. Coordenador (Docente + Admin)',
                'email' => 'docente.admin@pinc.edu.br',
                'telefone' => '(99) 99999-5555',
                'siape' => '9876543',
                'lattes' => 'http://lattes.cnpq.br/9876543210987654',
                'perfis' => [PerfilEnum::Docente->value, PerfilEnum::Administrador->value],
                'situacao' => true,
            ],
            // Novos Docentes para Teste
            [
                'cpf' => '66666666666',
                'nome' => 'Dra. Maria Helena Silva',
                'email' => 'maria.helena@pinc.edu.br',
                'telefone' => '(99) 98888-1001',
                'siape' => '3124567',
                'lattes' => 'http://lattes.cnpq.br/1111222233334444',
                'perfis' => [PerfilEnum::Docente->value],
                'situacao' => true,
            ],
            [
                'cpf' => '77777777777',
                'nome' => 'Prof. Carlos Eduardo Santos',
                'email' => 'carlos.eduardo@pinc.edu.br',
                'telefone' => '(99) 98888-1002',
                'siape' => '4235678',
                'lattes' => 'http://lattes.cnpq.br/2222333344445555',
                'perfis' => [PerfilEnum::Docente->value],
                'situacao' => true,
            ],
            [
                'cpf' => '88888888888',
                'nome' => 'Prof. Roberto Albuquerque',
                'email' => 'roberto.albuquerque@pinc.edu.br',
                'telefone' => '(99) 98888-1003',
                'siape' => '5346789',
                'lattes' => 'http://lattes.cnpq.br/3333444455556666',
                'perfis' => [PerfilEnum::Docente->value],
                'situacao' => false, // Inativo para teste
            ],
            // Novos Discentes para Teste
            [
                'cpf' => '99999999999',
                'nome' => 'Beatriz Costa Pereira',
                'email' => 'beatriz.costa@pinc.edu.br',
                'telefone' => '(99) 97777-2001',
                'sira' => '20261002',
                'curso_importado' => 'Ciência da Computação',
                'perfis' => [PerfilEnum::Discente->value],
                'situacao' => true,
            ],
            [
                'cpf' => '10101010101',
                'nome' => 'Lucas Ferreira Lima',
                'email' => 'lucas.lima@pinc.edu.br',
                'telefone' => '(99) 97777-2002',
                'sira' => '20261003',
                'curso_importado' => 'Engenharia de Software',
                'perfis' => [PerfilEnum::Discente->value],
                'situacao' => true,
            ],
            [
                'cpf' => '20202020202',
                'nome' => 'Juliana Martins Gomes',
                'email' => 'juliana.gomes@pinc.edu.br',
                'telefone' => '(99) 97777-2003',
                'sira' => '20261004',
                'curso_importado' => 'Sistemas de Informação',
                'perfis' => [PerfilEnum::Discente->value],
                'situacao' => true,
            ],
            [
                'cpf' => '30303030303',
                'nome' => 'Gabriel Henrique Oliveira',
                'email' => 'gabriel.oliveira@pinc.edu.br',
                'telefone' => '(99) 97777-2004',
                'sira' => '20261005',
                'curso_importado' => 'Análise e Desenvolvimento de Sistemas',
                'perfis' => [PerfilEnum::Discente->value],
                'situacao' => true,
            ],
            [
                'cpf' => '40404040404',
                'nome' => 'Fernanda Ribeiro Souza',
                'email' => 'fernanda.souza@pinc.edu.br',
                'telefone' => '(99) 97777-2005',
                'sira' => '20261006',
                'curso_importado' => 'Ciência da Computação',
                'perfis' => [PerfilEnum::Discente->value],
                'situacao' => false, // Inativo para teste
            ],
            // Novos Técnicos para Teste
            [
                'cpf' => '50505050505',
                'nome' => 'Ricardo Mendonça Filho',
                'email' => 'ricardo.mendonca@pinc.edu.br',
                'telefone' => '(99) 96666-3001',
                'siape' => '6457890',
                'perfis' => [PerfilEnum::Tecnico->value],
                'situacao' => true,
            ],
            [
                'cpf' => '60606060606',
                'nome' => 'Aline Cristina Rocha',
                'email' => 'aline.rocha@pinc.edu.br',
                'telefone' => '(99) 96666-3002',
                'siape' => '7568901',
                'perfis' => [PerfilEnum::Tecnico->value],
                'situacao' => true,
            ],
            [
                'cpf' => '70707070707',
                'nome' => 'Marcos Vinicius Barros',
                'email' => 'marcos.barros@pinc.edu.br',
                'telefone' => '(99) 96666-3003',
                'siape' => '8679012',
                'perfis' => [PerfilEnum::Tecnico->value],
                'situacao' => false, // Inativo para teste
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
            $dados['situacao'] = $dados['situacao'] ?? true;
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
