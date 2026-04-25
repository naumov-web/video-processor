<?php

namespace App\Models\Task\Enum;

enum TaskType:string
{
    case transcoding = 'transcoding';
    case thumbnail_generation = 'thumbnail_generation';
    case ai_tagging = 'ai_tagging';
}
