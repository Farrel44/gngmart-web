<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarouselSlideResource\Pages;
use App\Models\CarouselSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Filament Resource: Carousel Slides
 *
 * Mengelola banner carousel di homepage.
 * Admin bisa membuat slide baru, upload gambar, mengatur urutan,
 * dan mengaktifkan/menonaktifkan slide.
 */
class CarouselSlideResource extends Resource
{
    protected static ?string $model = CarouselSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Carousel';

    protected static ?string $modelLabel = 'Slide Carousel';

    protected static ?string $pluralModelLabel = 'Slide Carousel';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konten Slide')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->maxLength(255)
                            ->helperText('Judul utama slide (opsional)'),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subjudul')
                            ->maxLength(255)
                            ->helperText('Teks pendukung di bawah judul'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->helperText('Deskripsi tambahan (opsional)'),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('Gambar')
                            ->image()
                            ->required()
                            ->directory('carousel')
                            ->disk('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:5')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tombol CTA')
                    ->description('Tombol Call-to-Action pada slide')
                    ->schema([
                        Forms\Components\TextInput::make('button_text')
                            ->label('Teks Tombol')
                            ->maxLength(100)
                            ->placeholder('Lihat Promo'),

                        Forms\Components\TextInput::make('button_url')
                            ->label('URL Tombol')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://...')
                            ->helperText('URL tujuan saat tombol diklik'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('order_column')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih dulu'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Slide tidak aktif tidak akan tampil di homepage'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_column')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('image_path')
                    ->label('Gambar')
                    ->formatStateUsing(fn ($state) => $state ? '<img src="' . asset('storage/' . $state) . '" class="h-16 w-auto rounded object-cover" />' : 'No image')
                    ->html(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('(tanpa judul)'),

                Tables\Columns\TextColumn::make('button_text')
                    ->label('CTA')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order_column')
            ->reorderable('order_column')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarouselSlides::route('/'),
            'create' => Pages\CreateCarouselSlide::route('/create'),
            'edit' => Pages\EditCarouselSlide::route('/{record}/edit'),
        ];
    }
}
