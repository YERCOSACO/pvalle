<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionConductor extends Model
{
    protected $table = 'asignacion_conductores';

    protected $fillable = ['viaje_id', 'conductor_id', 'tipo_asignacion'];

    public function viaje()
    {
        return $this->belongsTo(Viaje::class);
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class);
    }
}