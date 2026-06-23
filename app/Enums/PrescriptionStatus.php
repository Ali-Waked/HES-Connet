<?php

namespace App\Enums;

enum PrescriptionStatus: string
{
    case ACTIVE = 'active';

    // المريض اختار صيدلية
    case PHARMACY_SELECTED = 'pharmacy_selected';

    // الصيدلية وافقت
    case ACCEPTED = 'accepted';

    // تم صرف الدواء
    case DISPENSED = 'dispensed';

    // تم رفض الطلب
    case REJECTED = 'rejected';

    // ألغيت الوصفة
    case CANCELLED = 'cancelled';
}
