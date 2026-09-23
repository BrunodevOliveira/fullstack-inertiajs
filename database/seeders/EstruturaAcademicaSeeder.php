<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Disciplina;
use Illuminate\Database\Seeder;

class EstruturaAcademicaSeeder extends Seeder
{
    public function run(): void
    {
        // Disciplina de referência padrão para os cursos (PINC 4 como limite)
        $disciplinaReferencia = Disciplina::where('nome', 'PINC 4')->first();

        $estrutura = [
            'Cidade Universitária (Fundão)' => [ //Campos
                'Ciências Biológicas: Modalidade Médica (Biomedicina)' => [ //Curso
                    'departamentos' => [ //Dados do curso
                        'Departamento de Bioquímica Médica Leopoldo de Meis',
                        'Departamento de Farmacologia Básica e Clínica',
                        'Departamento de Histologia e Embriologia',
                        'Laboratório de Neurobiologia Celular',
                    ],
                    'email' => 'biomedicina@icb.ufrj.br',
                    'colaborador' => true,
                ],
                'Medicina' => [
                    'departamentos' => [
                        'Departamento de Clínica Médica',
                        'Departamento de Cirurgia',
                        'Departamento de Pediatria',
                        'Departamento de Doenças Infecciosas e Parasitárias',
                    ],
                    'email' => 'medicina@medicina.ufrj.br',
                    'colaborador' => false,
                ],
                'Farmácia' => [
                    'departamentos' => [
                        'Departamento de Medicamentos',
                        'Departamento de Produtos Naturais e Alimentos',
                        'Laboratório de Tecnologia Farmacêutica',
                    ],
                    'email' => 'farmacia@pharma.ufrj.br',
                    'colaborador' => true,
                ],
            ],
            'Praia Vermelha' => [
                'Psicologia' => [
                    'departamentos' => [
                        'Departamento de Psicologia Geral e Experimental',
                        'Departamento de Psicologia Social',
                    ],
                    'email' => 'psicologia@ip.ufrj.br',
                    'colaborador' => false,
                ],
            ],
            'Macaé' => [
                'Medicina (Macaé)' => [
                    'departamentos' => [
                        'Coordenação Acadêmica de Medicina de Macaé',
                        'Laboratório Integrado de Ciências Médicas',
                    ],
                    'email' => 'medicina.macae@macae.ufrj.br',
                    'colaborador' => false,
                ],
            ],
            'Duque de Caxias' => [
                'Biotecnologia' => [
                    'departamentos' => [
                        'Polo de Biotecnologia e Nanotecnologia',
                    ],
                    'email' => 'biotec.caxias@caxias.ufrj.br',
                    'colaborador' => true,
                ],
            ],
        ];

        foreach ($estrutura as $nomeCampus => $cursos) {
            $campus = Campus::firstOrCreate(['nome' => $nomeCampus]);

            foreach ($cursos as $nomeCurso => $dadosCurso) {
                $curso = Curso::firstOrCreate(
                    [
                        'nome' => $nomeCurso,
                        'campus_id' => $campus->id,
                    ],
                    [
                        'disciplina_id' => $disciplinaReferencia?->id,
                        'email' => $dadosCurso['email'],
                        'colaborador' => $dadosCurso['colaborador'],
                    ]
                );

                foreach ($dadosCurso['departamentos'] as $nomeDepto) {
                    Departamento::firstOrCreate([
                        'nome' => $nomeDepto,
                        'curso_id' => $curso->id,
                    ]);
                }
            }
        }
    }

}
