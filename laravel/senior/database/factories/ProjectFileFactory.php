<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectFile>
 */
class ProjectFileFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->word().'.pdf';

        return [
            'project_id' => Project::factory(),
            'uploaded_by_id' => User::factory(),
            'disk' => 'local',
            'path' => 'projects/seed/'.fake()->uuid().'.pdf',
            'original_name' => $name,
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(1024, 2_000_000),
            'checksum' => hash('sha256', $name),
        ];
    }
}
