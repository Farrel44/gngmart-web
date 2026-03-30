<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            // Tidak ada delete action untuk order demi integritas data
        ];
    }

    /**
     * Validasi transisi status sebelum menyimpan.
     * Memastikan state machine rules dipatuhi.
     */
    protected function beforeSave(): void
    {
        $order = $this->record;
        $newStatus = $this->data['order_status'];

        // Jika status tidak berubah, skip validasi
        if ($order->order_status === $newStatus) {
            return;
        }

        // Validasi transisi menggunakan state machine
        if (! $order->canTransitionTo($newStatus)) {
            Notification::make()
                ->title('Transisi Status Tidak Valid')
                ->body("Tidak dapat mengubah status dari '{$order->getStatusLabel()}' ke '".Order::getStatusLabels()[$newStatus]."'.")
                ->danger()
                ->send();

            $this->halt();
        }
    }

    /**
     * Redirect ke halaman view setelah save (bukan list).
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
