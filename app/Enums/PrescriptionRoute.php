<?php

namespace App\Enums;

enum PrescriptionRoute: string
{
    case ORAL = 'oral';

    case INTRAVENOUS = 'intravenous';

    case INTRAMUSCULAR = 'intramuscular';

    case SUBCUTANEOUS = 'subcutaneous';

    case TOPICAL = 'topical';

    case INHALATION = 'inhalation';

    case RECTAL = 'rectal';

    case OPHTHALMIC = 'ophthalmic';

    case OTIC = 'otic';

    case NASAL = 'nasal';

    case SUBLINGUAL = 'sublingual';

    case TRANSDERMAL = 'transdermal';
}
