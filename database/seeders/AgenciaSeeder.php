<?php

namespace Database\Seeders;

use App\Enums\AgenciaTipoEnum;
use App\Models\Agencia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agencias = [
            [
                'sigla' => 'Sem bolsa',
                'nome' => 'Sem Bolsa (Voluntário / Outros)',
                'tipo' => AgenciaTipoEnum::Bolsista,
            ],
            [
                'sigla' => 'CNPq',
                'nome' => 'Conselho Nacional de Desenvolvimento Científico e Tecnológico',
                'tipo' => AgenciaTipoEnum::Ambos,
            ],
            [
                'sigla' => 'FAPERJ',
                'nome' => 'Fundação Carlos Chagas Filho de Amparo à Pesquisa do Estado do RJ',
                'tipo' => AgenciaTipoEnum::Ambos,
            ],
            [
                'sigla' => 'CAPES',
                'nome' => 'Coordenação de Aperfeiçoamento de Pessoal de Nível Superior',
                'tipo' => AgenciaTipoEnum::Bolsista,
            ],
            [
                'sigla' => 'UFRJ',
                'nome' => 'Universidade Federal do Rio de Janeiro (PIBIC / Institucional)',
                'tipo' => AgenciaTipoEnum::Bolsista,
            ],
        ];

        foreach($agencias as $agencia) {
            Agencia::firstOrCreate(
                [ 'sigla' => $agencia['sigla']], //condições para procurar
                [ // Dados para criar(como sigla foi utilizado no primeiro parâmetro, ele tbm já é inserido aqui):
                    'nome' => $agencia['nome'],
                    'tipo' => $agencia['tipo'],
                ]
            );
        }

    }
}
