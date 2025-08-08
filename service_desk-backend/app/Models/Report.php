<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'tblm_report';

    protected $primaryKey = 'id_report';

    public $timestamps = true;

    protected $fillable = [
        'nama_report',
        'inisial_report',
        'report_description',
        'ukuran_kertas',
        'layout_kertas',
        'query_report',
    ];
}