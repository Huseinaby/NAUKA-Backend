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
            'title' => $this->title,
            'description' => $this->description,
            'likes' => $this->likeBy()->count(),
            'image' => asset('storage/' . $this->image),
            'file' => asset('storage/' . $this->file),
            'video' => $this->video ? asset($this->video) : null,            
            'user' => [
                'id'=> $this->user_id,
                'name'=> $this->user->name,
                'photo_profile'=> asset( $this->user->photo_profile),
            ]
        ];
    }
}
