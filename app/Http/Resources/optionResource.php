<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class optionResource extends JsonResource
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
            'option_text' => $this->option_text,
            'option_image' => $this->option_image ? asset($this->option_image) : null,
            'is_correct' => $this->is_correct
        ];
    }
}
