<?php

namespace App\Imports\Api;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use App\Constants\GeneralConst;
use App\Models\Post;

class PostListImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation
{
    // use SkipsFailures;
    private $statusMap;
    private int $rowCount = 0;

    public function __construct()
    {
        $this->statusMap = array_flip(GeneralConst::POST_STATUS);
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255', 'unique:posts,title'],
            'description'   => ['required', 'string', 'max:255'],
            'status'        => ['required', 'string', 'in:'  . implode(',', array_values(GeneralConst::POST_STATUS))]
        ];
    }

    public function model(array $row)
    {
        ini_set('max_execution_time', 120);

        $this->rowCount++;

        return new Post([
            'title'  => $row['title'],
            'description'             => $row['description'],
            'status'            => $this->statusMap[trim($row['status'])],
            'created_user_id'   => auth('sanctum')->user()->id,
            'updated_user_id'   => auth('sanctum')->user()->id,
        ]);
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
