<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonerData extends Model
{
    protected $table = 'personer_data';

    protected $fillable = [
        'personnamn',
        'gatuadress',
        'postnummer',
        'postort',
        
        // Hitta data fields
        'hitta_data_id',
        'hitta_personnamn',
        'hitta_gatuadress',
        'hitta_postnummer',
        'hitta_postort',
        'hitta_alder',
        'hitta_kon',
        'hitta_telefon',
        'hitta_karta',
        'hitta_link',
        'hitta_bostadstyp',
        'hitta_bostadspris',
        'hitta_is_active',
        'hitta_is_telefon',
        'hitta_is_hus',
        
        // Merinfo data fields
        'merinfo_data_id',
        'merinfo_personnamn',
        'merinfo_alder',
        'merinfo_kon',
        'merinfo_gatuadress',
        'merinfo_postnummer',
        'merinfo_postort',
        'merinfo_telefon',
        'merinfo_karta',
        'merinfo_link',
        'merinfo_bostadstyp',
        'merinfo_bostadspris',
        'merinfo_is_active',
        'merinfo_is_telefon',
        'merinfo_is_hus',
        
        // Ratsit data fields
        'ratsit_data_id',
        'ratsit_gatuadress',
        'ratsit_postnummer',
        'ratsit_postort',
        'ratsit_forsamling',
        'ratsit_kommun',
        'ratsit_lan',
        'ratsit_adressandring',
        'ratsit_kommun_ratsit',
        'ratsit_stjarntacken',
        'ratsit_fodelsedag',
        'ratsit_personnummer',
        'ratsit_alder',
        'ratsit_kon',
        'ratsit_civilstand',
        'ratsit_fornamn',
        'ratsit_efternamn',
        'ratsit_personnamn',
        'ratsit_agandeform',
        'ratsit_bostadstyp',
        'ratsit_boarea',
        'ratsit_byggar',
        'ratsit_fastighet',
        'ratsit_telfonnummer',
        'ratsit_epost_adress',
        'ratsit_personer',
        'ratsit_foretag',
        'ratsit_grannar',
        'ratsit_fordon',
        'ratsit_hundar',
        'ratsit_bolagsengagemang',
        'ratsit_longitude',
        'ratsit_latitud',
        'ratsit_google_maps',
        'ratsit_google_streetview',
        'ratsit_ratsit_se',
        'ratsit_is_active',
        'ratsit_is_telefon',
        'ratsit_is_hus',
        
        'is_active',
    ];

    protected $casts = [
        // JSON fields
        'merinfo_telefon' => 'array',
        'hitta_telefon' => 'array',
        'ratsit_telfonnummer' => 'array',
        'ratsit_epost_adress' => 'array',
        'ratsit_personer' => 'array',
        'ratsit_foretag' => 'array',
        'ratsit_grannar' => 'array',
        'ratsit_fordon' => 'array',
        'ratsit_hundar' => 'array',
        'ratsit_bolagsengagemang' => 'array',
        
        // Boolean fields
        'hitta_is_active' => 'boolean',
        'hitta_is_telefon' => 'boolean',
        'hitta_is_hus' => 'boolean',
        'merinfo_is_active' => 'boolean',
        'merinfo_is_telefon' => 'boolean',
        'merinfo_is_hus' => 'boolean',
        'ratsit_is_active' => 'boolean',
        'ratsit_is_telefon' => 'boolean',
        'ratsit_is_hus' => 'boolean',
        'is_active' => 'boolean',
        
        // Timestamp fields
        'hitta_created_at' => 'datetime',
        'hitta_updated_at' => 'datetime',
        'merinfo_created_at' => 'datetime',
        'merinfo_updated_at' => 'datetime',
        'ratsit_created_at' => 'datetime',
        'ratsit_updated_at' => 'datetime',
    ];

    // Disable automatic timestamps since we have custom timestamp columns
    public $timestamps = true;
}
