<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';
    protected static ?string $title       = 'Variants & Stock';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->placeholder('e.g. Large – Walnut')->columnSpanFull(),
            TextInput::make('sku')->required()->unique(ignoreRecord: true)->label('SKU'),
            Select::make('size')->options([
                'XS' => 'Extra Small', 'S' => 'Small',
                'M'  => 'Medium',      'L' => 'Large', 'XL' => 'Extra Large',
            ]),
            TextInput::make('price_override')->numeric()->prefix('KES')
                ->label('Price override')->helperText('Leave empty to use product base price.'),
            TextInput::make('stock_qty')->integer()->required()->default(0)->label('Stock qty'),
            TextInput::make('low_stock_threshold')->integer()->default(5)->label('Low stock alert at'),
            TextInput::make('weight_kg')->numeric()->suffix('kg'),
            Toggle::make('is_active')->default(true),
            TextInput::make('sort_order')->integer()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('sku')->copyable()->color('gray'),
                TextColumn::make('size')->badge(),
                TextColumn::make('effective_price')->label('Price')->money('KES')
                    ->getStateUsing(fn ($record) => $record->effective_price),
                TextColumn::make('stock_qty')->label('Stock')->badge()
                    ->color(fn ($state, $record) => match(true) {
                        $state === 0                            => 'danger',
                        $state <= $record->low_stock_threshold => 'warning',
                        default                                => 'success',
                    }),
                IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                Action::make('restock')
                    ->label('Add stock')
                    ->icon(Heroicon::PlusCircle)
                    ->color('success')
                    ->form([
                        TextInput::make('qty')->label('Quantity to add')->integer()->required()->minValue(1),
                        Textarea::make('note')->label('Note (optional)')->rows(2),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->addStock((int) $data['qty'], 'restock', $data['note'] ?? '', Auth::id());
                        Notification::make()->title("Added {$data['qty']} units to {$record->name}")->success()->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
