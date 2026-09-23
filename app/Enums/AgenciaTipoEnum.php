<?php

namespace App\Enums;

enum AgenciaTipoEnum: int
{
    case Bolsista = 1;
    case Projeto = 2;
    case Ambos = 3;

    public function label(): string
    {
        return match ($this) {
            self::Bolsista => 'Bolsista',
            self::Projeto => 'Projeto',
            self::Ambos => 'Ambos (Bolsa e Projeto)',
        };
    }

    /**
     * Retorna a severidade visual para uso em Badges e Tags do PrimeVue.
     */
    public function badgeSeverity(): string
    {
        return match ($this) {
            self::Bolsista => 'info',
            self::Projeto => 'warn',
            self::Ambos => 'success',
        };
    }

    /**
     * Retorna lista de opções formatadas para selects/dropdowns.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }
}

