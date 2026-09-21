<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest 
{
  public function authorize(): bool 
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'nome_social' => ['nullable', 'string', 'max:255'],
      'telefone' => ['nullable', 'string', 'max:20'],
      'lattes' => ['nullable', 'url', 'max:255'],
    ];
  }

  public function messages(): array
    {
        return [
            'lattes.url' => 'Informe uma URL válida para o Currículo Lattes (ex: http://lattes.cnpq.br/...).',
            'telefone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'nome_social.max' => 'O nome social não pode ter mais de 255 caracteres.',
        ];
    }
}
/**
 *   authorize()
    ↓
    "Pode fazer?"

    rules()
        ↓
    "O que precisa ser válido?"

    messages()
        ↓
    "Qual mensagem mostrar se falhar?"

    validated() -> chamado no controller
        ↓
    "Me dê os dados que passaram."
 */