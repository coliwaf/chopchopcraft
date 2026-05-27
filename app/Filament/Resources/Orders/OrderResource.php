<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Jobs\SendWhatsAppOrderConfirmation;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model           = Order::class;
    // protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;
    public static function getNavigationGroup(): ?string
    {
        return 'Shop';
    }
    protected static ?int    $navigationSort  = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) Order::where('status', OrderStatus::Pending)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order details')
                ->columns(3)
                ->schema([
                    TextInput::make('order_number')->disabled(),
                    Select::make('status')
                        ->options(OrderStatus::options())
                        ->required(),
                    Select::make('payment_status')
                        ->options(collect(PaymentStatus::cases())
                            ->mapWithKeys(fn($c) => [$c->value => ucfirst($c->value)]))
                        ->required(),
                    TextInput::make('tracking_number')->columnSpan(2),
                    Textarea::make('internal_notes')->rows(3)->columnSpanFull(),
                ]),

            Section::make('Shipping address')
                ->columns(2)
                ->collapsed()
                ->schema([
                    TextInput::make('shipping_name'),
                    TextInput::make('shipping_phone'),
                    TextInput::make('shipping_address_line1')->label('Address 1'),
                    TextInput::make('shipping_address_line2')->label('Address 2'),
                    TextInput::make('shipping_city')->label('City'),
                    TextInput::make('shipping_county')->label('County'),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order summary')
                ->columns(3)
                ->schema([
                    TextEntry::make('order_number')->weight('bold'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn($state) => $state instanceof OrderStatus ? $state->color() : 'gray'),
                    TextEntry::make('payment_status')
                        ->badge()
                        ->color(fn($state) => $state instanceof PaymentStatus ? $state->color() : 'gray'),
                    TextEntry::make('subtotal')->money('KES'),
                    TextEntry::make('discount_amount')->money('KES'),
                    TextEntry::make('total')->money('KES')->weight('bold'),
                    TextEntry::make('payment_method'),
                    TextEntry::make('created_at')->dateTime('d M Y, H:i'),
                    TextEntry::make('tracking_number')->default('—'),
                ]),

            Section::make('Customer')
                ->columns(2)
                ->schema([
                    TextEntry::make('customer.full_name')->label('Name'),
                    TextEntry::make('customer.email')->label('Email'),
                    TextEntry::make('customer.phone')->label('Phone'),
                    TextEntry::make('shipping_city')->label('City'),
                    TextEntry::make('shipping_county')->label('County'),
                    TextEntry::make('shipping_address_line1')->label('Address'),
                ]),

            Section::make('Items')
                ->schema([
                    RepeatableEntry::make('items')
                        ->schema([
                            TextEntry::make('product_name'),
                            TextEntry::make('variant_name'),
                            TextEntry::make('qty'),
                            TextEntry::make('unit_price')->money('KES'),
                            TextEntry::make('line_total')->money('KES'),
                        ])
                        ->columns(5),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()->weight('bold')->copyable(),

                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable(['first_name', 'last_name'])
                    ->description(fn(Order $r) => $r->customer?->phone),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => $state instanceof OrderStatus ? $state->color() : 'gray'),

                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn($state) => $state instanceof PaymentStatus ? $state->color() : 'gray'),

                TextColumn::make('payment_method'),

                TextColumn::make('total')->money('KES')->sortable(),

                TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(OrderStatus::options()),
                SelectFilter::make('payment_status')
                    ->options(collect(PaymentStatus::cases())
                        ->mapWithKeys(fn($c) => [$c->value => ucfirst($c->value)])),
                SelectFilter::make('payment_method')
                    ->options(['mpesa' => 'M-Pesa', 'stripe' => 'Stripe', 'paypal' => 'PayPal', 'whatsapp' => 'WhatsApp']),
                Filter::make('today')
                    ->query(fn(Builder $q) => $q->whereDate('created_at', today()))
                    ->label('Today only'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                \Filament\Actions\Action::make('send_whatsapp')
                    ->label('Resend WA')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        SendWhatsAppOrderConfirmation::dispatch($record);
                        Notification::make()->title('WhatsApp notification queued')->success()->send();
                    }),

                \Filament\Actions\Action::make('update_status')
                    ->label('Update status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Select::make('status')->options(OrderStatus::options())->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update(['status' => $data['status']]);
                        SendWhatsAppOrderConfirmation::dispatch($record);
                        Notification::make()->title('Status updated')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view'  => ViewOrder::route('/{record}'),
            'edit'  => EditOrder::route('/{record}/edit'),
        ];
    }
}
