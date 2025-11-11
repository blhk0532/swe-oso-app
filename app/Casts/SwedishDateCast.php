<?php

namespace App\Casts;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class SwedishDateCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }

        // If it's already a Carbon instance, return it
        if ($value instanceof Carbon) {
            return $value;
        }

        // If it's already a valid date string that Carbon can parse, use it
        try {
            return Carbon::parse($value);
        } catch (Exception $e) {
            // If standard parsing fails, try Swedish format
        }

        // Swedish month names mapping
        $swedishMonths = [
            'januari' => 'January',
            'februari' => 'February',
            'mars' => 'March',
            'april' => 'April',
            'maj' => 'May',
            'juni' => 'June',
            'juli' => 'July',
            'augusti' => 'August',
            'september' => 'September',
            'oktober' => 'October',
            'november' => 'November',
            'december' => 'December',
        ];

        // Replace Swedish month names with English ones
        $englishDate = str_replace(array_keys($swedishMonths), array_values($swedishMonths), $value);

        try {
            return Carbon::parse($englishDate);
        } catch (Exception $e) {
            // If all parsing fails, return null or throw an exception
            throw new InvalidArgumentException("Unable to parse date: {$value}");
        }
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        // If it's a string, try to parse it and format it
        try {
            $date = $this->get($model, $key, $value, $attributes);

            return $date?->format('Y-m-d');
        } catch (Exception $e) {
            return $value; // Return as-is if parsing fails
        }
    }
}
