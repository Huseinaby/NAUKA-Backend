<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChoiceResource extends JsonResource
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
            'choice_text' => $this->choice_text,
            'choice_image' => $this->choice_image ? asset($this->choice_image) : null,
            'is_correct' => $this->is_correct,
        ];
    }
}
