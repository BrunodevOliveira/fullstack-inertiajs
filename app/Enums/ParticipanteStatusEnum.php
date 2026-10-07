<?php

namespace App\Enums;

enum ParticipanteStatusEnum: int
{
    case Ativo = 0;
    case Entrando = 1;
    case Saindo = 2;
    case Historico = 3;

    public function label(): string
    {
        return match ($this) {
            self::Ativo => 'Ativo',
            self::Entrando => 'Entrando (aguardando aceite)',
            self::Saindo => 'Saindo (em desligamento)',
            self::Historico => 'Arquivado/concluído'
        };
    }

    /**
     * Retorna a severidade visual para uso em Badges e Tags do PrimeVue.
     */
    public function badgeSeverity(): string
    {
        return match ($this) {
            self::Ativo => 'success',
            self::Entrando => 'warn',
            self::Saindo => 'danger',
            self::Historico => 'secondary',
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
