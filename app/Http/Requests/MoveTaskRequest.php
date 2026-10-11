<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the policy decides in the controller
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Task::MOVABLE)],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
