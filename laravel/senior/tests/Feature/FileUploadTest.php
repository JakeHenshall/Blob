<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_upload_is_stored_and_recorded(): void
    {
        Storage::fake('local');

        $manager = User::factory()->manager()->create();
        $project = Project::factory()->create(['owner_id' => $manager->id]);

        $this->actingAs($manager)
            ->post("/projects/{$project->id}/files", [
                'file' => UploadedFile::fake()->create('spec.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('project_files', 1);
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'project.file_uploaded',
        ]);
    }

    public function test_disallowed_mime_type_is_rejected(): void
    {
        Storage::fake('local');

        $manager = User::factory()->manager()->create();
        $project = Project::factory()->create(['owner_id' => $manager->id]);

        $this->actingAs($manager)
            ->post("/projects/{$project->id}/files", [
                'file' => UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('project_files', 0);
    }
}
