<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use App\Constants\GeneralConst;

class TableColumnResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $columns = [
            "table_name" => $this->table_name,
            "column_name" => $this->column_name,
            "data_type" => $this->data_type,
            "is_nullable" => $this->is_nullable,
            "column_default" => $this->column_default,
            "character_maximum_length" => $this->character_maximum_length,
            "column_comment" => $this->column_comment,
            "label" => Str::headline($this->column_name),
            "html_input_type" => GeneralConst::TABLE_COLUMN_TYPES_TO_HTML_INPUT[$this->data_type] ?? 'text'
        ];
        if (array_key_exists($this->column_name, GeneralConst::SELECT_OPTIONS_COLUMNS)) {
            $columns['html_input_type'] = 'select';
            $columns['options'] = (object)GeneralConst::SELECT_OPTIONS_COLUMNS[$this->column_name] ?? [];
        }
        return $columns;
    }
}
