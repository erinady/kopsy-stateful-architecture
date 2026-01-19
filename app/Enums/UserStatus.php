<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'Aktif';
    case INACTIVE = 'Tidak Aktif';
    case RESIGNED = 'Mengundurkan Diri';
    case INREVIEW = 'Dalam Peninjauan';
    case PENDING = 'Menunggu Pembayaran';
    case REJECTED = 'Ditolak dengan alasan';
}
