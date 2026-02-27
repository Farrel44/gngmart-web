<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            // Verifikasi Pembayaran — hanya muncul jika payment pending
            Actions\Action::make('verify_payment')
                ->label('Verifikasi Pembayaran')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Pembayaran')
                ->modalDescription('Yakin ingin memverifikasi pembayaran ini? Status order akan otomatis berubah menjadi "Sudah Dibayar".')
                ->visible(fn (): bool => $this->record->payment?->payment_status === Payment::STATUS_PENDING)
                ->action(function (): void {
                    $this->record->payment->update([
                        'payment_status' => Payment::STATUS_SUCCESS,
                    ]);
                    $this->record->transitionTo(Order::STATUS_PAID);
                }),

            // Proses Pesanan
            Actions\Action::make('process_order')
                ->label('Proses Pesanan')
                ->icon('heroicon-o-cog')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->canTransitionTo(Order::STATUS_PROCESSING))
                ->action(fn () => $this->record->transitionTo(Order::STATUS_PROCESSING)),

            // Kirim Pesanan
            Actions\Action::make('ship_order')
                ->label('Kirim Pesanan')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->canTransitionTo(Order::STATUS_SHIPPED))
                ->action(fn () => $this->record->transitionTo(Order::STATUS_SHIPPED)),

            // Selesaikan Pesanan
            Actions\Action::make('complete_order')
                ->label('Pesanan Selesai')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->canTransitionTo(Order::STATUS_COMPLETED))
                ->action(fn () => $this->record->transitionTo(Order::STATUS_COMPLETED)),
        ];
    }
}
