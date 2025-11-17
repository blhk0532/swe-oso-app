<?php

namespace App\Filament\Resources\RatsitDatas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RatsitDataInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Person Information')
                            ->schema([
                                TextEntry::make('personnamn')
                                    ->label('Full Name'),

                                TextEntry::make('fornamn')
                                    ->label('First Name'),

                                TextEntry::make('efternamn')
                                    ->label('Last Name'),

                                TextEntry::make('personnummer')
                                    ->label('Social Security Number'),

                                TextEntry::make('fodelsedag')
                                    ->label('Date of Birth')
                                    ->date(),

                                TextEntry::make('alder')
                                    ->label('Age'),

                                TextEntry::make('kon')
                                    ->label('Sex'),

                                TextEntry::make('civilstand')
                                    ->label('Marital Status'),

                                TextEntry::make('stjarntacken')
                                    ->label('Star Sign'),

                                TextEntry::make('telefon')
                                    ->label('Phone Number')
                                    ->formatStateUsing(function ($state) {
                                        return $state ?: '-';
                                    }),

                                TextEntry::make('telfonnummer')
                                    ->label('Additional Phone Numbers')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return implode(', ', $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return implode(', ', $decoded);
                                            }

                                            return str_replace('|', ', ', $state);
                                        }

                                        return '-';
                                    }),

                                TextEntry::make('epost_adress')
                                    ->label('Email Addresses')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return implode(', ', $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return implode(', ', $decoded);
                                            }

                                            return str_replace('|', ', ', $state);
                                        }

                                        return '-';
                                    }),
                            ])
                            ->columns(2)
                            ->collapsible(),

                        Section::make('Address Information')
                            ->schema([
                                TextEntry::make('gatuadress')
                                    ->label('Street Address')
                                    ->columnSpanFull(),

                                TextEntry::make('postnummer')
                                    ->label('Postal Code'),

                                TextEntry::make('postort')
                                    ->label('City'),

                                TextEntry::make('forsamling')
                                    ->label('Parish'),

                                TextEntry::make('kommun')
                                    ->label('Municipality'),

                                TextEntry::make('lan')
                                    ->label('State'),

                                TextEntry::make('fastighet')
                                    ->label('Fastighetsbetäckning')
                                    ->columnSpanFull(),

                                TextEntry::make('longitude')
                                    ->label('Longitude'),

                                TextEntry::make('latitud')
                                    ->label('Latitude'),

                                TextEntry::make('google_maps')
                                    ->label('Google Maps')
                                    ->url(fn ($record) => $record->google_maps)
                                    ->openUrlInNewTab()
                                    ->formatStateUsing(fn ($state) => $state ? '🗺️ Map' : '-'),

                                TextEntry::make('google_streetview')
                                    ->label('Street View')
                                    ->url(fn ($record) => $record->google_streetview)
                                    ->openUrlInNewTab()
                                    ->formatStateUsing(fn ($state) => $state ? '📸 View' : '-'),

                                TextEntry::make('ratsit_se')
                                    ->label('Ratsit Link')
                                    ->url(fn ($record) => $record->ratsit_se)
                                    ->openUrlInNewTab()
                                    ->formatStateUsing(fn ($state) => $state ? '🔗 Ratsit' : '-'),
                            ])
                            ->columns(2)
                            ->collapsible(),

                        Section::make('Address Property Details')
                            ->schema([
                                TextEntry::make('agandeform')
                                    ->label('Form of Ownership'),

                                TextEntry::make('bostadstyp')
                                    ->label('Housing Type'),

                                TextEntry::make('boarea')
                                    ->label('Living Area'),

                                TextEntry::make('byggar')
                                    ->label('Year of Construction'),

                                TextEntry::make('personer')
                                    ->label('Persons at Address')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return collect($state)->map(function ($item) {
                                                if (is_array($item) && isset($item['text'])) {
                                                    $text = $item['text'];
                                                    $link = $item['link'] ?? null;

                                                    return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                }

                                                return is_string($item) ? $item : (string) $item;
                                            })->implode(', ');
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return collect($decoded)->map(function ($item) {
                                                    if (is_array($item) && isset($item['text'])) {
                                                        $text = $item['text'];
                                                        $link = $item['link'] ?? null;

                                                        return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                    }

                                                    return is_string($item) ? $item : (string) $item;
                                                })->implode(', ');
                                            }

                                            return str_replace('|', ', ', $state);
                                        }

                                        return '-';
                                    })
                                    ->html()
                                    ->columnSpanFull(),

                                TextEntry::make('foretag')
                                    ->label('Companies at Address')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return collect($state)->map(function ($item) {
                                                if (is_array($item) && isset($item['text'])) {
                                                    $text = $item['text'];
                                                    $link = $item['link'] ?? null;

                                                    return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                }

                                                return is_string($item) ? $item : (string) $item;
                                            })->implode(', ');
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return collect($decoded)->map(function ($item) {
                                                    if (is_array($item) && isset($item['text'])) {
                                                        $text = $item['text'];
                                                        $link = $item['link'] ?? null;

                                                        return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                    }

                                                    return is_string($item) ? $item : (string) $item;
                                                })->implode(', ');
                                            }

                                            return str_replace('|', ', ', $state);
                                        }

                                        return '-';
                                    })
                                    ->html()
                                    ->columnSpanFull(),

                                TextEntry::make('grannar')
                                    ->label('Neighbors')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return collect($state)->map(function ($item) {
                                                if (is_array($item) && isset($item['text'])) {
                                                    $text = $item['text'];
                                                    $link = $item['link'] ?? null;

                                                    return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                }

                                                return is_string($item) ? $item : (string) $item;
                                            })->implode(', ');
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return collect($decoded)->map(function ($item) {
                                                    if (is_array($item) && isset($item['text'])) {
                                                        $text = $item['text'];
                                                        $link = $item['link'] ?? null;

                                                        return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                    }

                                                    return is_string($item) ? $item : (string) $item;
                                                })->implode(', ');
                                            }

                                            return str_replace('|', ', ', $state);
                                        }

                                        return '-';
                                    })
                                    ->html()
                                    ->columnSpanFull(),

                                TextEntry::make('fordon')
                                    ->label('Vehicles')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return collect($state)->map(function ($item) {
                                                if (is_array($item) && isset($item['text'])) {
                                                    $text = $item['text'];
                                                    $link = $item['link'] ?? null;

                                                    return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                }

                                                return is_string($item) ? $item : (string) $item;
                                            })->implode(', ');
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return collect($decoded)->map(function ($item) {
                                                    if (is_array($item) && isset($item['text'])) {
                                                        $text = $item['text'];
                                                        $link = $item['link'] ?? null;

                                                        return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                    }

                                                    return is_string($item) ? $item : (string) $item;
                                                })->implode(', ');
                                            }

                                            return str_replace('|', ', ', $state);
                                        }

                                        return '-';
                                    })
                                    ->html()
                                    ->columnSpanFull(),

                                TextEntry::make('hundar')
                                    ->label('Dogs')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return collect($state)->map(function ($item) {
                                                if (is_array($item) && isset($item['text'])) {
                                                    $text = $item['text'];
                                                    $link = $item['link'] ?? null;

                                                    return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                }

                                                return is_string($item) ? $item : (string) $item;
                                            })->implode(', ');
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return collect($decoded)->map(function ($item) {
                                                    if (is_array($item) && isset($item['text'])) {
                                                        $text = $item['text'];
                                                        $link = $item['link'] ?? null;

                                                        return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                    }

                                                    return is_string($item) ? $item : (string) $item;
                                                })->implode(', ');
                                            }

                                            return str_replace('|', ', ', $state);
                                        }

                                        return '-';
                                    })
                                    ->html()
                                    ->columnSpanFull(),

                                TextEntry::make('bolagsengagemang')
                                    ->label('Corporate Commitments')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return collect($state)->map(function ($item) {
                                                if (is_array($item) && isset($item['text'])) {
                                                    $text = $item['text'];
                                                    $link = $item['link'] ?? null;

                                                    return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                }

                                                return is_string($item) ? $item : (string) $item;
                                            })->implode(', ');
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return collect($decoded)->map(function ($item) {
                                                    if (is_array($item) && isset($item['text'])) {
                                                        $text = $item['text'];
                                                        $link = $item['link'] ?? null;

                                                        return $link ? "<a href='{$link}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$text}</a>" : $text;
                                                    }

                                                    return is_string($item) ? $item : (string) $item;
                                                })->implode(', ');
                                            }

                                            return str_replace('|', ', ', $state);
                                        }

                                        return '-';
                                    })
                                    ->html()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                TextEntry::make('is_active')
                                    ->label('Active')
                                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),

                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->label('Updated')
                                    ->dateTime(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
