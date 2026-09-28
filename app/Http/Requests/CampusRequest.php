<?php

namespace App\Http\Requests;

use App\Models\Campus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampusRequest extends FormRequest
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
        $campus = $this->route('campus');
        $campusId = $campus instanceof Campus ? $campus->id : $campus;

        return [
            'nome' => [
                'required',
                'string',
                'max:100',
                Rule::unique('campuses', 'nome') //O nome deve ser único na tabela campuses, considerando a coluna nome,
                    ->ignore($campusId) //exceto o próprio campus que está sendo editado
                    ->withoutTrashed(), //ignorando os campi que foram excluídos via Soft Delete.
            ],
        ];
    }

    /**
     * Mensagens de validação personalizadas em português.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do campus é obrigatório.',
            'nome.string' => 'O nome do campus deve ser um texto válido.',
            'nome.max' => 'O nome do campus não pode ultrapassar 100 caracteres.',
            'nome.unique' => 'Já existe um campus ativo cadastrado com este nome.',
        ];
    }
}
