<?php

namespace App\Http\Requests;

use App\Models\Disciplina;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DisciplinaRequest extends FormRequest
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
        $disciplina = $this->route('disciplina');
        $disciplinaId = $disciplina instanceof Disciplina ? $disciplina->id : $disciplina;

        return [
            'nome' => [
                'required',
                'string',
                'max:10',
                Rule::unique('disciplinas', 'nome')
                    ->ignore($disciplinaId)
                    ->withoutTrashed(),
            ],
        ];
    }

    /**
     * Mensagens de validação em português.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da disciplina/período é obrigatório.',
            'nome.string' => 'O nome da disciplina/período deve ser um texto válido.',
            'nome.max' => 'O nome da disciplina/período não pode ultrapassar 10 caracteres.',
            'nome.unique' => 'Já existe uma disciplina/período ativa com este nome.',
        ];
    }
}
