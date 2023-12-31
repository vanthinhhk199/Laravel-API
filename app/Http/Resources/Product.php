<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Product extends JsonResource
{

    public function toArray($request) {
        return [
          'id' => $this->id,
          'tensp' => $this->name,
          'gia' => $this->price,
          'created_at' => $this->created_at->format('d/m/Y'),
          'updated_at' => $this->updated_at->format('d/m/Y'),
        ];
      }
}
?>