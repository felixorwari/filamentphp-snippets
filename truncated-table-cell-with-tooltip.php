<?php

TextColumn::make('description')
    ->limit(50)
    ->tooltip(function (TextColumn $column): ?string {
        $state = $column->getState();

        if (strlen($state) <= $column->getCharacterLimit()) {
            return null;
        }

        // Only render the tooltip if the column contents exceeds the length limit.
        return $state;
}),
