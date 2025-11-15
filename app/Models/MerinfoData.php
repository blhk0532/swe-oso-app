<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerinfoData extends Model
{
    // Use default connection; previously forced 'sqlite' which broke API tests under mysql.

    protected $table = 'merinfo_data';

    protected $fillable = [
        'personnamn',
        'alder',
        'kon',
        'gatuadress',
        'postnummer',
        'postort',
        'telefon',
        'karta',
        'link',
        'bostadstyp',
        'bostadspris',
        'is_active',
        'is_telefon',
        'is_ratsit',
        'is_hus',
        'merinfo_personer_total',
        'merinfo_foretag_total',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_telefon' => 'boolean',
        'is_ratsit' => 'boolean',
        'is_hus' => 'boolean',
        'telefon' => 'array',
        'merinfo_personer_total' => 'integer',
        'merinfo_foretag_total' => 'integer',
    ];

    /**
     * Truncated preview of the telefon field for table display.
     * Returns an em dash when empty or placeholder.
     */
    public function getTelefonPreviewAttribute(): string
    {
        $raw = $this->telefon; // Casted to array if JSON, else mixed

        if (is_array($raw)) {
            $phoneStr = implode(' | ', $raw);
        } else {
            $phoneStr = trim(preg_replace('/\s+/', ' ', (string) $raw));
        }

        if ($phoneStr === '' || $phoneStr === 'Lägg till telefonnummer') {
            return '—';
        }

        return mb_strlen($phoneStr) > 13 ? mb_substr($phoneStr, 0, 13) . '…' : $phoneStr;
    }
}
