<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('customer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('discount_code_id')
                    ->numeric(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ])
                    ->default('pending')
                    ->required(),
                Select::make('payment_method')
                    ->options([
            'mpesa' => 'Mpesa',
            'stripe' => 'Stripe',
            'paypal' => 'Paypal',
            'whatsapp' => 'Whatsapp',
            'unpaid' => 'Unpaid',
        ])
                    ->default('unpaid')
                    ->required(),
                Select::make('payment_status')
                    ->options([
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            'partial' => 'Partial',
        ])
                    ->default('pending')
                    ->required(),
                TextInput::make('shipping_name')
                    ->required(),
                TextInput::make('shipping_phone')
                    ->tel(),
                TextInput::make('shipping_address_line1')
                    ->required(),
                TextInput::make('shipping_address_line2'),
                TextInput::make('shipping_city')
                    ->required(),
                TextInput::make('shipping_county'),
                TextInput::make('shipping_postal_code'),
                TextInput::make('shipping_country')
                    ->required()
                    ->default('KE'),
                DateTimePicker::make('whatsapp_confirmation_sent_at'),
                DateTimePicker::make('whatsapp_order_sent_at'),
                Textarea::make('internal_notes')
                    ->columnSpanFull(),
                TextInput::make('tracking_number'),
            ]);
    }
}
