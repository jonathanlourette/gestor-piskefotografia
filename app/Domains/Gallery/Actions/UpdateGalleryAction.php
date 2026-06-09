<?php

declare(strict_types=1);

namespace App\Domains\Gallery\Actions;

use App\Domains\Gallery\Gallery;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class UpdateGalleryAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            return DB::transaction(function () {
                $gallery = Gallery::findOrFail($this->data->get('id'));

                $gallery->title = strip_tags($this->data->get('title'));
                $gallery->customer_name = strip_tags($this->data->get('customer_name'));
                $gallery->customer_email = strip_tags($this->data->get('customer_email'));
                $gallery->customer_phone = strip_tags($this->data->get('customer_phone'));
                $gallery->expires_at = $this->data->get('expires_at');

                $password = $this->data->get('password');
                if (! empty($password)) {
                    $gallery->password = bcrypt($password);
                }

                $gallery->save();

                return $gallery->fresh();
            });
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Galeria não encontrada.');
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível atualizar a galeria. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'id' => ['required', 'integer', 'exists:galleries,id'],
            'title' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'expires_at' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:4', 'max:20'],
        ], [
            'id.required' => 'O ID da galeria é obrigatório.',
            'id.exists' => 'Galeria não encontrada.',
            'title.required' => 'O título da galeria é obrigatório.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'customer_name.required' => 'O nome do cliente é obrigatório.',
            'customer_name.max' => 'O nome do cliente não pode ter mais de 255 caracteres.',
            'customer_email.required' => 'O e-mail do cliente é obrigatório.',
            'customer_email.email' => 'Informe um e-mail válido.',
            'customer_email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
            'customer_phone.required' => 'O telefone do cliente é obrigatório.',
            'customer_phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'expires_at.date' => 'A data de expiração deve ser uma data válida.',
            'password.min' => 'A senha deve ter no mínimo 4 caracteres.',
            'password.max' => 'A senha não pode ter mais de 20 caracteres.',
        ]);
    }
}
