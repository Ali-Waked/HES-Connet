<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PENDINGREVIEW = 'pending_review';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case REJECTED = 'rejected';
}
