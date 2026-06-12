<?php

declare(strict_types=1);

namespace App\Domains\Order\Enums;

enum OrderStatusEnum: int
{
    case ENVIADO = 1;
    case PAGO = 2;
    case REVELANDO = 3;
    case CONCLUIDO = 4;
    case PROCESSANDO = 5;
    case PROCESSADO = 6;

    /**
     * Retorna o rótulo em português do status.
     */
    public function label(): string
    {
        return match ($this) {
            self::ENVIADO => 'Enviado',
            self::PAGO => 'Pago',
            self::REVELANDO => 'Revelando',
            self::CONCLUIDO => 'Concluído',
            self::PROCESSANDO => 'Processando',
            self::PROCESSADO => 'Processado',
        };
    }

    /**
     * Retorna a classe de cor Bootstrap para badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::ENVIADO => 'warning',
            self::PAGO => 'primary',
            self::REVELANDO => 'info',
            self::CONCLUIDO => 'success',
            self::PROCESSANDO => 'secondary',
            self::PROCESSADO => 'success',
        };
    }

    /**
     * Retorna o ícone Bootstrap Icons para o status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::ENVIADO => 'bi-inbox',
            self::PAGO => 'bi-check-circle',
            self::REVELANDO => 'bi-camera',
            self::CONCLUIDO => 'bi-trophy',
            self::PROCESSANDO => 'bi-gear',
            self::PROCESSADO => 'bi-check2-all',
        };
    }

    /**
     * Retorna a descrição do status para o cliente.
     */
    public function description(): string
    {
        return match ($this) {
            self::ENVIADO => 'Seu pedido foi recebido e está aguardando confirmação de pagamento.',
            self::PAGO => 'Pagamento confirmado! Seu pedido será iniciado em breve.',
            self::REVELANDO => 'Suas fotos estão sendo reveladas com qualidade profissional.',
            self::CONCLUIDO => 'Suas fotos estão prontas! Entre em contato para combinar a entrega.',
            self::PROCESSANDO => 'As fotos estão sendo processadas. Aguarde a conclusão.',
            self::PROCESSADO => 'Todas as fotos foram processadas com sucesso.',
        };
    }

    /**
     * Retorna array de opções para selects HTML (todos os statuses).
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            self::ENVIADO->value => self::ENVIADO->label(),
            self::PAGO->value => self::PAGO->label(),
            self::REVELANDO->value => self::REVELANDO->label(),
            self::CONCLUIDO->value => self::CONCLUIDO->label(),
            self::PROCESSANDO->value => self::PROCESSANDO->label(),
            self::PROCESSADO->value => self::PROCESSADO->label(),
        ];
    }

    /**
     * Retorna array de opções para o select de status do admin.
     * O admin pode manualmente definir: Pago, Revelando ou Concluído.
     *
     * @return array<int, string>
     */
    public static function adminOptions(): array
    {
        return [
            self::PAGO->value => self::PAGO->label(),
            self::REVELANDO->value => self::REVELANDO->label(),
            self::CONCLUIDO->value => self::CONCLUIDO->label(),
        ];
    }
}
