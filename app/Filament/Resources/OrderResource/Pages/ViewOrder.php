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

            // Action 1: Verifikasi Pembayaran (PENDING → PROCESSING)
            Actions\Action::make('verify_payment_pending')
                ->label('Verifikasi Bayar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Pembayaran')
                ->modalDescription('Verifikasi pembayaran pembeli? Status order akan berubah menjadi "Diproses".')
                ->modalSubmitActionLabel('Ya, Verifikasi')
                ->visible(fn (): bool => 
                    $this->record->order_status === Order::STATUS_PENDING
                    && $this->record->payment?->payment_status === Payment::STATUS_PENDING
                    && $this->record->payment?->payment_method !== Payment::METHOD_MIDTRANS)
                ->action(function (): void {
                    // Hanya ubah order status ke PROCESSING, payment tetap PENDING (untuk COD)
                    $this->record->transitionTo(Order::STATUS_PROCESSING);
                }),

            // Action 2: Kirim Pesanan (PROCESSING → SHIPPED)
            Actions\Action::make('ship_order')
                ->label('Kirim')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Kirim Pesanan')
                ->modalDescription('Yakin pesanan sudah dikirim?')
                ->visible(fn (): bool => $this->record->order_status === Order::STATUS_PROCESSING)
                ->action(fn () => $this->record->transitionTo(Order::STATUS_SHIPPED)),

            // Action 3: Verifikasi Pembayaran Tunai (SHIPPED, payment PENDING → SUCCESS + SHIPPED → PAID)
            Actions\Action::make('confirm_cod_payment')
                ->label('Verifikasi Bayar')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Pembayaran Tunai')
                ->modalDescription('Konfirmasi pembayaran tunai sudah diterima dari pembeli?')
                ->modalSubmitActionLabel('Ya, Sudah Diterima')
                ->visible(fn (): bool => 
                    $this->record->order_status === Order::STATUS_SHIPPED
                    && $this->record->payment?->payment_status === Payment::STATUS_PENDING
                    && $this->record->payment?->payment_method !== Payment::METHOD_MIDTRANS)
                ->action(function (): void {
                    // Update payment status ke success
                    $this->record->payment->update([
                        'payment_status' => Payment::STATUS_SUCCESS,
                    ]);
                    // Transition order dari SHIPPED ke PAID
                    $this->record->transitionTo(Order::STATUS_PAID);
                }),

            // Action 4: Pesanan Selesai (PAID → COMPLETED)
            Actions\Action::make('complete_order')
                ->label('Selesai')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Selesaikan Pesanan')
                ->modalDescription('Yakin pesanan sudah diterima oleh pembeli?')
                ->visible(fn (): bool => 
                    $this->record->order_status === Order::STATUS_PAID)
                ->action(fn () => $this->record->transitionTo(Order::STATUS_COMPLETED)),
        ];
    }
}
