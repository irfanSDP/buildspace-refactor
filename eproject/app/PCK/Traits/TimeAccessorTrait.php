<?php namespace PCK\Traits;

use Carbon\Carbon;

/**
 * Enables accessing the date attributes if there is trailing data
 * due to how some models may have timestamps with microseconds
 * while the other models may not be saved with microseconds.
 */
trait TimeAccessorTrait {

    public function getDates()
    {
        // Returns only the manually specified date fields.
        // Ignores the created_at, updated_at and updated_at fields.
        return $this->dates;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getDeletedAtAttribute($value)
    {
        return Carbon::parse($value);
    }

}