<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statamic_roles', function (Blueprint $table) {
            $table->id();
            $table->string('handle')->unique();
            $table->string('title');
            $table->json('permissions')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('statamic_roles')->insert([
            [
                'handle' => 'blog_editor',
                'title' => 'Blog Editor',
                'permissions' => json_encode([
                    'access cp',
                    'view blogs entries',
                    'edit blogs entries',
                    'create blogs entries',
                    'delete blogs entries',
                    'publish blogs entries',
                    'reorder blogs entries',
                    'edit other authors blogs entries',
                    'publish other authors blogs entries',
                    'delete other authors blogs entries',
                    'view app assets',
                    'upload app assets',
                    'view s3 assets',
                    'upload s3 assets',
                ]),
                'preferences' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'handle' => 'content_editor',
                'title' => 'Content Editor',
                'permissions' => json_encode([
                    'view events entries',
                    'edit events entries',
                    'create events entries',
                    'delete events entries',
                    'publish events entries',
                    'reorder events entries',
                    'edit other authors events entries',
                    'publish other authors events entries',
                    'view experiences entries',
                    'edit experiences entries',
                    'create experiences entries',
                    'delete experiences entries',
                    'publish experiences entries',
                    'reorder experiences entries',
                    'edit other authors experiences entries',
                    'publish other authors experiences entries',
                    'view authors entries',
                    'edit authors entries',
                    'create authors entries',
                    'delete authors entries',
                    'publish authors entries',
                    'reorder authors entries',
                    'edit other authors authors entries',
                    'publish other authors authors entries',
                    'view prayers entries',
                    'edit prayers entries',
                    'create prayers entries',
                    'delete prayers entries',
                    'publish prayers entries',
                    'reorder prayers entries',
                    'edit other authors prayers entries',
                    'publish other authors prayers entries',
                    'view alerts entries',
                    'edit alerts entries',
                    'create alerts entries',
                    'delete alerts entries',
                    'publish alerts entries',
                    'reorder alerts entries',
                    'edit other authors alerts entries',
                    'publish other authors alerts entries',
                    'view books entries',
                    'edit books entries',
                    'create books entries',
                    'delete books entries',
                    'publish books entries',
                    'reorder books entries',
                    'edit other authors books entries',
                    'publish other authors books entries',
                    'view media_item entries',
                    'edit media_item entries',
                    'create media_item entries',
                    'delete media_item entries',
                    'publish media_item entries',
                    'reorder media_item entries',
                    'edit other authors media_item entries',
                    'publish other authors media_item entries',
                    'view media_series entries',
                    'edit media_series entries',
                    'create media_series entries',
                    'delete media_series entries',
                    'publish media_series entries',
                    'reorder media_series entries',
                    'edit other authors media_series entries',
                    'publish other authors media_series entries',
                    'view podcasts entries',
                    'edit podcasts entries',
                    'create podcasts entries',
                    'delete podcasts entries',
                    'publish podcasts entries',
                    'reorder podcasts entries',
                    'edit other authors podcasts entries',
                    'publish other authors podcasts entries',
                    'view blogs entries',
                    'edit blogs entries',
                    'create blogs entries',
                    'delete blogs entries',
                    'publish blogs entries',
                    'reorder blogs entries',
                    'edit other authors blogs entries',
                    'publish other authors blogs entries',
                    'view pages entries',
                    'edit pages entries',
                    'create pages entries',
                    'delete pages entries',
                    'publish pages entries',
                    'reorder pages entries',
                    'edit other authors pages entries',
                    'publish other authors pages entries',
                    'view topics terms',
                    'edit topics terms',
                    'create topics terms',
                    'view app assets',
                    'upload app assets',
                    'edit app assets',
                    'move app assets',
                    'rename app assets',
                    'delete app assets',
                    'view s3 assets',
                    'upload s3 assets',
                    'edit s3 assets',
                    'move s3 assets',
                    'rename s3 assets',
                    'delete s3 assets',
                ]),
                'preferences' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statamic_roles');
    }
};
