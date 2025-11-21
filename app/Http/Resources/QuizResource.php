<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
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
            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,
            'quiz_text' => $this->quiz_text,
            'quiz_image' => $this->quiz_image ? asset($this->quiz_image) : null,
            'choices' => ChoiceResource::collection($this->whenLoaded('choices')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'=> $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
