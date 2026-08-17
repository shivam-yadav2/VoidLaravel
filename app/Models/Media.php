<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['path', 'original_name', 'mime_type', 'size', 'alt_text', 'uploaded_by'];

    public function url(): string
    {
        return Storage::url($this->path);
    }

    public function humanSize(): string
    {
        $kb = $this->size / 1024;

        return $kb < 1024
            ? number_format($kb, 0).' KB'
            : number_format($kb / 1024, 1).' MB';
    }
}
