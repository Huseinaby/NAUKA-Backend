<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
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
            'likes' => $this->likeBy()->count(),
            'description' => $this->description,
            'video_url' => asset('storage/' . $this->video),
            'user' => [
                'id'=> $this->user_id,
                'name'=> $this->user->name,
                'photo_profile'=> $this->user->photo_profile ? asset($this->user->photo_profile) : null,
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
