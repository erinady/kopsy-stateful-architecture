<?php

namespace App\Enums;

enum UserRole: string
{
    case DPS = 'Dewan Pengawas Syariah';
    case PENGAWAS = 'Pengawas';
    case KETUA = 'Ketua';
    case SEKRETARIS = 'Sekretaris';
    case BENDAHARA = 'Bendahara';
    case SEKSIMURABAH = 'Seksi Murabahah';
    case SEKSIAMDK = 'Seksi AMDK';
    case PJANGGOTA = 'Penanggung Jawab Anggota';
    case ANGGOTA = 'Anggota';
}
