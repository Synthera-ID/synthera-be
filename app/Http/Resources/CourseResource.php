<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            "title" => $this->title,
            "slug" => $this->slug,
            "description" => $this->description,
            "thumbnail_url" => $this->thumbnail_url,
            "content_url" => $this->content_url,
            "video_url" => $this->video_url,
            "tag" => $this->tag,
            "min_tier" => $this->min_tier,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "category" => [
                "id" => $this->category->id,
                "name" => $this->category->name,
                "slug" => $this->category->slug
            ]
        ];
    }
}
