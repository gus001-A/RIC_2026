<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $table = 'cuentas';
    protected $primaryKey = 'id_cuenta';

    protected $fillable = [
        'id_empresa',
        'codigo_cuenta',
        'nombre_cuenta',
        'descripcion',
        'Naturaleza',
        'tipo_cuenta',
        'id_cuenta_madre',
        'en_uso',
        'es_cuenta_resultados',
        'saldo_inicial',
        'nivel',
        // ✅ CAMPOS DEL SISTEMA ANTIGUO - ESTOS SON LOS QUE FALTAN
        'indice_c',
        'clave_c',
        'madre_c',
        'empresa_c',
        'origen_c',
        'depreciacion_c',
        'usuario_c',
        'fecha_c',
        'id_empresa_c',
        'fondeo_c',
        'cuenta_resultados',
        'regimen_c',
        'check_resultados',
        'check_uso'
    ];

    protected $casts = [
        'fecha_c' => 'datetime',
    ];

    // Relaciones
    public function cuentaMadre()
    {
        return $this->belongsTo(Cuenta::class, 'id_cuenta_madre', 'id_cuenta');
    }

    public function cuentasHijas()
    {
        return $this->hasMany(Cuenta::class, 'id_cuenta_madre', 'id_cuenta');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id');
    }

    /**
     * ✅ Obtener nombre completo (código + nombre)
     */
    public function getNombreCompletoAttribute()
    {
        return $this->codigo_cuenta . ' - ' . $this->nombre_cuenta;
    }

    /**
     * ✅ Obtener el nivel jerárquico usando indice_c si existe
     * Si no existe indice_c, construirlo manualmente
     */
    public function getNivelJerarquicoAttribute()
    {
        // ✅ PRIMERO: Si tiene indice_c, usarlo directamente
        if (!empty($this->indice_c)) {
            return $this->indice_c;
        }
        
        // Si no tiene indice_c, construir manualmente
        $empresaId = $this->id_empresa;
        
        if ($this->nivel == 0) {
            return (string)$empresaId;
        }
        
        $jerarquia = [(string)$empresaId];
        $cuentaActual = $this;
        $codigos = [];
        $contador = 0;
        $maxIteraciones = 10;
        
        while ($cuentaActual && $cuentaActual->nivel > 0 && $contador < $maxIteraciones) {
            $codigoCuenta = $cuentaActual->codigo_cuenta;
            $numero = preg_replace('/[^0-9]/', '', $codigoCuenta);
            if (empty($numero)) {
                $numero = str_pad($cuentaActual->id_cuenta, 3, '0', STR_PAD_LEFT);
            }
            $numero = str_pad($numero, 3, '0', STR_PAD_LEFT);
            array_unshift($codigos, $numero);
            
            if ($cuentaActual->id_cuenta_madre && $cuentaActual->id_cuenta_madre != $cuentaActual->id_cuenta) {
                $cuentaActual = Cuenta::find($cuentaActual->id_cuenta_madre);
            } else {
                break;
            }
            $contador++;
        }
        
        foreach ($codigos as $codigo) {
            $jerarquia[] = $codigo;
        }
        
        return implode('-', $jerarquia);
    }

    public function getNivelTextoAttribute()
    {
        $niveles = ['', 'Primer Nivel', 'Segundo Nivel', 'Tercer Nivel', 'Cuarto Nivel', 'Quinto Nivel'];
        return $niveles[$this->nivel] ?? "Nivel {$this->nivel}";
    }

    // Scopes
    public function scopeEnUso($query)
    {
        return $query->where('en_uso', true);
    }

    public function scopeDeudoras($query)
    {
        return $query->where('naturaleza', 'DEUDORA');
    }

    public function scopeAcreedoras($query)
    {
        return $query->where('naturaleza', 'ACREEDORA');
    }
}