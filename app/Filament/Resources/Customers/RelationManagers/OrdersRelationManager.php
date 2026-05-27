<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title       = 'Order history';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('order_number')->searchable()->weight('bold')->copyable(),
                TextColumn::make('status')->badge()
                    ->color(fn ($state) => $state instanceof OrderStatus ? $state->color() : 'gray'),
                TextColumn::make('payment_status')->badge()
                    ->color(fn ($state) => $state instanceof PaymentStatus ? $state->color() : 'gray'),
                TextColumn::make('total')->money('KES')->sortable(),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('view')
                    ->url(fn ($record) => route('filament.admin.resources.orders.view', $record))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}
