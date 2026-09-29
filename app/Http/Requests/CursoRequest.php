<?php

namespace App\Http\Requests;

use App\Models\Curso;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CursoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para criação e edição de cursos.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $curso = $this->route('curso');
        $cursoId = $curso instanceof Curso ? $curso->id : $curso;

        return [
            'nome' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cursos', 'nome')
                    ->where(fn ($query) => $query->where('campus_id', $this->campus_id))
                    ->ignore($cursoId)
                    ->withoutTrashed(),
            ],
            'campus_id' => [
                'required',
                'integer',
                Rule::exists('campuses', 'id')->withoutTrashed(), // O campus selecionado precisa existir e estar ativo
            ],
            'disciplina_id' => [
                'nullable',
                'integer',
                Rule::exists('disciplinas', 'id'),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'colaborador' => [
                'boolean',
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
            'nome.required' => 'O nome do curso é obrigatório.',
            'nome.string' => 'O nome do curso deve ser um texto válido.',
            'nome.max' => 'O nome do curso não pode ultrapassar 100 caracteres.',
            'nome.unique' => 'Já existe um curso ativo cadastrado com este nome neste campus.',
            'campus_id.required' => 'O campus é obrigatório.',
            'campus_id.exists' => 'O campus selecionado não foi encontrado ou está inativo.',
            'disciplina_id.exists' => 'A disciplina de referência selecionada é inválida.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.max' => 'O e-mail não pode ultrapassar 255 caracteres.',
            'colaborador.boolean' => 'O valor do campo colaborador deve ser verdadeiro ou falso.',
        ];
    }
}
