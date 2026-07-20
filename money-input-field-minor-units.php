<?php

Select::make('price_currency')
    ->label('Currency')
    ->options([
        'KES' => 'KES (KSh)',
        'JPY' => 'JPY (¥)',
        'USD' => 'USD ($)',
    ])
    ->default('KES')
    ->live()
    ->partiallyRenderComponentsAfterStateUpdated([
        'price_amount',
        'discount_price_amount',
    ])
    ->required()
    ->columnSpan(1),

// Current price, displayed as actual price 
TextInput::make('price_minor_units')
    ->label('Price')
    ->prefix(fn (Get $get): ?string => $get('price_currency')) // update with selected currency
    ->placeholder('e.g. 1500000')
    ->formatStateUsing(
        fn (?int $state): ?float => $state !== null
            ? $state / 100
            : null
    ) // display amount in major units
    ->dehydrateStateUsing(
        fn ($state) => $state !== null
            ? (int) round($state * 100)
            : null
    ) // persist amount in minor units
    ->required()
    ->minValue(0)
    ->numeric()
    ->columnSpan([
        'default' => 1,
        'lg' => 2,
    ]),

// Original price, usually displayed as crossed out
// Suitable for when discounts are available, or pricees changed
TextInput::make('original_price_minor_units')
    ->label('Discounted price')
    ->prefix(fn (Get $get): ?string => $get('price_currency'))
    ->helperText('If a discount price is set, the actual price will be displayed as struck through.')
    ->placeholder('e.g. 1400000')
    ->formatStateUsing(
        fn (?int $state): ?float => $state !== null
            ? $state / 100
            : null
    ) // display amount in major units
    ->dehydrateStateUsing(
        fn ($state) => $state !== null
            ? (int) round($state * 100)
            : null
    ) // persist amount in minor units
    ->minValue(0)
    ->numeric()
    ->columnSpan([
        'default' => 1,
        'lg' => 2,
    ]),
