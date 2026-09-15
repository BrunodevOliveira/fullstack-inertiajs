<?php

namespace App\Enums;

enum PerfilEnum: int
{
    case Root = 1;
    case Administrador = 2;
    case Docente = 3;
    case Discente = 4;
    case Tecnico = 5;

    public function label(): string
    {
        return match ($this) {
            self::Root => 'Super Administrador',
            self::Administrador => 'Administrador',
            self::Docente => 'Docente / Orientador',
            self::Discente => 'Discente / Aluno',
            self::Tecnico => 'Técnico Administrativo',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Root => 'Acesso irrestrito a todas as funções e configurações do sistema.',
            self::Administrador => 'Gestão acadêmica, aprovação de projetos, relatórios e controle de usuários.',
            self::Docente => 'Criação e coordenação de projetos de pesquisa e avaliação de bolsistas.',
            self::Discente => 'Participação em projetos de iniciação científica e submissão de relatórios.',
            self::Tecnico => 'Apoio técnico a projetos, laboratórios e suporte a orientadores.',
        };
    }

    /**
     * Retorna a severidade visual para uso em Badges e Tags do PrimeVue.
     */
    public function badgeSeverity(): string
    {
        return match ($this) {
            self::Root => 'danger',
            self::Administrador => 'warn',
            self::Docente => 'info',
            self::Discente => 'success',
            self::Tecnico => 'secondary',
        };
    }
}
