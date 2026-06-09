<?php

declare(strict_types=1);

namespace App\Domains\Gallery\Actions;

use App\Domains\Gallery\Enums\GalleryStatusEnum;
use App\Domains\Gallery\Gallery;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateGalleryAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            return DB::transaction(function () {
                $gallery = new Gallery;
                $gallery->title = strip_tags($this->data->get('title'));
                $gallery->customer_name = strip_tags($this->data->get('customer_name'));
                $gallery->customer_email = strip_tags($this->data->get('customer_email'));
                $gallery->customer_phone = strip_tags($this->data->get('customer_phone'));

                $password = $this->data->get('password');
                $plainPassword = $password ?? Str::password(6, letters: true, numbers: true, symbols: false);
                $gallery->password = bcrypt($plainPassword);

                $gallery->expires_at = $this->data->get('expires_at');
                $gallery->status = GalleryStatusEnum::DRAFT;
                $gallery->photos_count = 0;
                $gallery->save();

                return [
                    'gallery' => $gallery,
                    'plain_password' => $plainPassword,
                ];
            });
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível criar a galeria. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'title' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'expires_at' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:4', 'max:20'],
        ], [
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
