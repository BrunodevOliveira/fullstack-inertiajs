<?php

namespace App\Http\Requests;

use App\Enums\AgenciaTipoEnum;
use App\Models\Agencia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgenciaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $agencia = $this->route('agencia');
        $agenciaId = $agencia instanceof Agencia ? $agencia->id : $agencia;

        return [
            'sigla' => [
                'required',
                'string',
                'max:20',
                Rule::unique('agencias', 'sigla')
                    ->ignore($agenciaId)
                    ->withoutTrashed(),
            ],
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('agencias', 'nome')
                    ->ignore($agenciaId)
                    ->withoutTrashed(),
            ],
            'tipo' => [
                'required',
                Rule::enum(AgenciaTipoEnum::class),
            ],
        ];
    }

    /**
     * Mensagens amigáveis de validação em português.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sigla.required' => 'A sigla da agência é obrigatória.',
            'sigla.string' => 'A sigla da agência deve ser um texto válido.',
            'sigla.max' => 'A sigla da agência não pode ultrapassar 20 caracteres.',
            'sigla.unique' => 'Já existe uma agência de fomento ativa com esta sigla.',
            'nome.required' => 'O nome da agência é obrigatório.',
            'nome.string' => 'O nome da agência deve ser um texto válido.',
            'nome.max' => 'O nome da agência não pode ultrapassar 255 caracteres.',
            'nome.unique' => 'Já existe uma agência de fomento ativa com este nome.',
            'tipo.required' => 'O tipo de fomento é obrigatório.',
            'tipo.Illuminate\Validation\Rules\Enum' => 'O tipo de fomento selecionado é inválido.',
        ];
    }
}
