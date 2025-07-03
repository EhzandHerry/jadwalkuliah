<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Available extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    // PENAMBAHAN: Memberi tahu model ini untuk menggunakan tabel 'ketersediaan'
    protected $table = 'ketersediaan';

    protected $fillable = [
        'id_dosen',
        'hari',
        'waktu_mulai',
        'waktu_selesai',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
