<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjetoRequest extends FormRequest
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
        return [
            'titulo' => [
                'required',
                'string',
                'max:300',
            ],
            'assunto' => [
                'required',
                'string',
                'max:500',
            ],
            'descricao' => [
                'required',
                'string',
            ],
            'vagas' => [
                'required',
                'integer',
                'min:1',
            ],
            'departamento_id' => [
                'required',
                'integer',
                Rule::exists('departamentos', 'id')->withoutTrashed(),
            ],
            'agencia_id' => [
                'nullable',
                'integer',
                Rule::exists('agencias', 'id')->withoutTrashed(),
            ],

        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Título
            'titulo.required' => 'O campo título é obrigatório.',
            'titulo.string' => 'O título deve ser um texto válido.',
            'titulo.max' => 'O título não pode ter mais de 300 caracteres.',

            // Assunto
            'assunto.required' => 'O campo assunto é obrigatório.',
            'assunto.string' => 'O assunto deve ser um texto válido.',
            'assunto.max' => 'O assunto não pode ter mais de 500 caracteres.',

            // Descrição
            'descricao.required' => 'O campo descrição é obrigatório.',
            'descricao.string' => 'A descrição deve ser um texto válido.',

            // Vagas
            'vagas.required' => 'O campo vagas é obrigatório.',
            'vagas.integer' => 'O número de vagas deve ser um número inteiro.',
            'vagas.min' => 'O número de vagas deve ser no mínimo 1.',

            // Departamento ID
            'departamento_id.required' => 'O departamento é obrigatório.',
            'departamento_id.integer' => 'O departamento selecionado é inválido.',
            'departamento_id.exists' => 'O departamento selecionado não existe.',

            // Agência ID
            'agencia_id.integer' => 'A agência selecionada é inválida.',
            'agencia_id.exists' => 'A agência selecionada não existe.',
        ];
    }
}
