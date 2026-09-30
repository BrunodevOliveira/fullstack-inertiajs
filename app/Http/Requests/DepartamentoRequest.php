<?php

namespace App\Http\Requests;

use App\Models\Departamento;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartamentoRequest extends FormRequest
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
        $departamento = $this->route('departamento');
        $departamentoId = $departamento instanceof Departamento ? $departamento->id : $departamento;

        return [
            'nome' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departamentos', 'nome')
                    ->where(fn ($query) => $query->where('curso_id', $this->curso_id))
                    ->ignore($departamentoId)
                    ->withoutTrashed(),
            ],
            'curso_id' => [
                'required',
                'integer',
                Rule::exists('cursos', 'id')->withoutTrashed(),
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
            'nome.required' => 'O nome do departamento/laboratório é obrigatório.',
            'nome.string' => 'O nome do departamento/laboratório deve ser um texto válido.',
            'nome.max' => 'O nome do departamento/laboratório não pode ultrapassar 100 caracteres.',
            'nome.unique' => 'Já existe um departamento/laboratório cadastrado com este nome para este curso.',
            'curso_id.required' => 'O curso ao qual o departamento pertence é obrigatório.',
            'curso_id.integer' => 'O identificador do curso deve ser um número inteiro.',
            'curso_id.exists' => 'O curso selecionado não foi encontrado ou está inativo.',
        ];
    }
}
