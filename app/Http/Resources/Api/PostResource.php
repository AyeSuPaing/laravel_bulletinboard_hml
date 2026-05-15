<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Constants\GeneralConst;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "status" => GeneralConst::POST_STATUS[$this->status] ?? null,
            "created_at" => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            "updated_at" => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s'),
            "deleted_at" => Carbon::parse($this->deleted_at)->format('d-m-Y H:i:s'),
            "created_user" => $this->createdUser->name ?? null,
            "updated_user" => $this->updatedUser->name ?? null,
            "deleted_user" => $this->deletedUser->name ?? null
        ];
    }
}
