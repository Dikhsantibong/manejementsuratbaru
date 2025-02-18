<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Perda extends Model
{
    use HasFactory;

    protected $table = 'perda';
    
    protected $fillable = [
        'no_agenda',
        'no_surat',
        'pengirim',
        'tanggal_surat',
        'tanggal_terima',
        'perihal',
        'lampiran',
        'catatan',
    ];

    protected $casts = [    
        'tanggal_surat' => 'date',      
        'tanggal_terima' => 'date',
    ];

    public function getTanggalSuratAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getTanggalTerimaAttribute($value)
    {
        return Carbon::parse($value);
    }
}
