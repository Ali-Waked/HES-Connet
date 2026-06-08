# Translation Integration Guide

This project uses `spatie/laravel-translatable` for handling multi-language content.

## Configuration
- **Default Locale:** `en`
- **Fallback Locale:** `en`
- **Supported Locales:** `en`, `ar`

## Searching Translated Fields

To search in translated fields across all supported languages, use Laravel's JSON query syntax:

### Example: Searching for an Organization by Name

```php
use App\Models\Organization;

$searchTerm = 'Health';

$organizations = Organization::where('name->en', 'like', "%{$searchTerm}%")
    ->orWhere('name->ar', 'like', "%{$searchTerm}%")
    ->get();
```

### Advanced: Searching in Current Locale Only

```php
use App\Models\Article;
use Illuminate\Support\Facades\App;

$searchTerm = 'Medicine';
$locale = App::getLocale();

$articles = Article::where("title->{$locale}", 'like', "%{$searchTerm}%")
    ->get();
```

## API Usage

### Public API
Returns only the translated value for the current locale.
Locale is determined by:
1. `?lang=ar` query parameter.
2. `Accept-Language: ar` header.

**Response Example:**
```json
{
    "id": 1,
    "name": "Central Hospital"
}
```

### Admin API
Returns all available translations for administrative purposes.
Detected by route prefix `/api/admin/*` or user role.

**Response Example:**
```json
{
    "id": 1,
    "name": {
        "en": "Central Hospital",
        "ar": "المستشفى المركزي"
    }
}
```

## Adding New Translatable Fields
1. Add the field to the `$translatable` array in the Model.
2. Ensure the database column type is `json`.
3. (Optional) Run a migration to convert existing data if necessary.
