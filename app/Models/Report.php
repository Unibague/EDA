<?php

namespace App\Models;

use App\Exports\CommitmentsReport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;

class Report extends Model
{

    public static function downloadCommitmentsExcel(\Illuminate\Support\Collection $commitments){
        $headers = ['Funcionario', 'Dependencia', 'Competencia','Compromiso', 'Fecha de finalización', 'Realizado', 'Archivos subidos', 'Fecha de realización'];
        return Excel::download(new CommitmentsReport($commitments,$headers),  'Reporte_Compromisos_EDA.xlsx');
    }

    use HasFactory;
}
