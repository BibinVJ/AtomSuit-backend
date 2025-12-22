<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueInTrash implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        protected string $table,
        protected string $column = 'NULL',
        protected mixed $ignoreId = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $column = $this->column === 'NULL' ? $attribute : $this->column;

        $query = DB::table($this->table)->where($column, $value);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        $record = $query->first();

        if ($record) {
            // Check if the record is soft deleted (if deleted_at column exists)
            // Note: DB::table includes trashed items by default (it's raw DB access)

            if (isset($record->deleted_at) && $record->deleted_at !== null) {
                $fail("The {$column} is unavailable because it exists in the trash. Please restore it or permanently delete it.");
            } else {
                $fail("The {$attribute} has already been taken.");
            }
        }
    }
}
