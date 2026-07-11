<?php

// amount_paid is stored in minor units
TextColumn::make('amount_paid')
  ->alignRight()
  ->fontFamily(FontFamily::Mono)
  ->money(
      currency: fn($record) => $record->currency, // assumed currency field exists in the schema
      divideBy: 100, // convert to major units
      locale: null, // examples: gb, nl, us
      decimalPlaces: 2
  )
  ->sortable(),
