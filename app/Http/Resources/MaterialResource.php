<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'likes' => $this->likes,
            'image' => asset('storage/' . $this->image),
            'file' => asset('storage/' . $this->file),
            'video' => $this->video ? asset($this->video) : null,
        ];
    }
}
