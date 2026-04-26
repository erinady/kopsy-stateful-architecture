<?php

namespace App\Enums;

enum MemberStatusEnum: string
{
    case PAYMENT_PENDING = 'Menunggu Pembayaran';
    case ACTIVE = 'Aktif';
    case RESIGNED_REQUESTED = 'Pengunduran Diri Diajukan';
    case RESIGNED_REJECTED = 'Pengunduran Diri Ditolak';
}
