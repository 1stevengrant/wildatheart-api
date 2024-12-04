<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('campfire_id');
                $table->string('mapbox_place_name')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('street_address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('state_code')->nullable();
                $table->string('zip')->nullable();
                $table->string('country_code')->nullable();
                $table->string('country')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });

            Schema::create('attendees', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('campfire_id', 36);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('given_name')->nullable();
                $table->string('family_name')->nullable();
                $table->string('email')->nullable();
                $table->string('country')->nullable();
                $table->string('country_code')->nullable();
                $table->string('phone')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->boolean('marketing_updates')->default(true);
                $table->timestamps();
            });

            Schema::create('book_shipments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('campfire_id', 36);
                $table->string('tracking_number');
                $table->longText('notes')->nullable();
                $table->timestamps();
            });

            Schema::create('campfires', function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->string('user_id')->nullable();
                $table->longText('description')->nullable();
                $table->string('organization')->nullable();
                $table->string('name')->nullable();
                $table->string('phone')->nullable();
                $table->string('frequency')->default('weekly');
                $table->string('mapbox_place_name')->nullable();
                $table->string('street_address')->nullable();
                $table->string('state')->nullable();
                $table->string('state_code')->nullable();
                $table->string('city')->nullable();
                $table->string('zip')->nullable();
                $table->string('country')->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->boolean('gift_requested')->default(false);
                $table->boolean('gift_sent')->default(false);
                $table->boolean('is_existing')->default(false);
                $table->unsignedBigInteger('book_request_count')->default(0);
                $table->boolean('is_private')->default(false);
                $table->boolean('is_indoor')->default(false);
                $table->timestamp('approved_at')->nullable();
                $table->boolean('welcome_sent')->default(false);
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->unsignedBigInteger('attendee_limit')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });

            Schema::create('email_verifies', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id');
                $table->string('campfire_id')->nullable();
                $table->string('token');
                $table->dateTime('expires_at');
                $table->timestamps();
            });

            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });

            Schema::create('issues', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('email');
                $table->string('name');
                $table->string('subject');
                $table->longText('description');
                $table->timestamps();
            });

            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');

                $table->index(['model_id', 'model_type']);
                $table->primary(['permission_id', 'model_id', 'model_type']);
            });

            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');

                $table->index(['model_id', 'model_type']);
                $table->primary(['role_id', 'model_id', 'model_type']);
            });

            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });

            Schema::create('permissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();

                $table->unique(['name', 'guard_name']);
            });

            Schema::create('photos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('path');
                $table->timestamps();
            });

            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id')->index('role_has_permissions_role_id_foreign');

                $table->primary(['permission_id', 'role_id']);
            });

            Schema::create('roles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();

                $table->unique(['name', 'guard_name']);
            });

            Schema::create('states', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('code');
                $table->timestamps();
            });

            Schema::create('users', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('uuid', 36)->nullable()->unique();
                $table->string('name')->nullable();
                $table->string('given_name')->nullable();
                $table->string('family_name')->nullable();
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('country_code')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('image')->nullable();
                $table->string('optimized_image')->nullable();
                $table->string('provider_id')->nullable();
                $table->string('provider')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->dateTime('last_login_at')->nullable();
                $table->string('salesforce_id')->nullable();
                $table->unsignedInteger('profile_step')->nullable()->default(1);
                $table->boolean('opted_into_marketing')->default(true);
                $table->rememberToken();
                $table->timestamps();
            });

            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->foreign(['permission_id'])->references(['id'])->on('permissions')->onUpdate('NO ACTION')->onDelete('CASCADE');
            });

            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->foreign(['role_id'])->references(['id'])->on('roles')->onUpdate('NO ACTION')->onDelete('CASCADE');
            });

            Schema::table('role_has_permissions', function (Blueprint $table) {
                $table->foreign(['permission_id'])->references(['id'])->on('permissions')->onUpdate('NO ACTION')->onDelete('CASCADE');
                $table->foreign(['role_id'])->references(['id'])->on('roles')->onUpdate('NO ACTION')->onDelete('CASCADE');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropForeign('role_has_permissions_permission_id_foreign');
            $table->dropForeign('role_has_permissions_role_id_foreign');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign('model_has_roles_role_id_foreign');
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign('model_has_permissions_permission_id_foreign');
        });

        Schema::dropIfExists('users');

        Schema::dropIfExists('states');

        Schema::dropIfExists('roles');

        Schema::dropIfExists('role_has_permissions');

        Schema::dropIfExists('photos');

        Schema::dropIfExists('personal_access_tokens');

        Schema::dropIfExists('permissions');

        Schema::dropIfExists('password_reset_tokens');

        Schema::dropIfExists('nova_pending_field_attachments');

        Schema::dropIfExists('nova_notifications');

        Schema::dropIfExists('nova_field_attachments');

        Schema::dropIfExists('model_has_roles');

        Schema::dropIfExists('model_has_permissions');

        Schema::dropIfExists('issues');

        Schema::dropIfExists('failed_jobs');

        Schema::dropIfExists('email_verifies');

        Schema::dropIfExists('campfires');

        Schema::dropIfExists('book_shipments');

        Schema::dropIfExists('attendees');

        Schema::dropIfExists('addresses');

        Schema::dropIfExists('action_events');
    }
};
