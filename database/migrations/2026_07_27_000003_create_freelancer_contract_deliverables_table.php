<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_contract_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->constrained('freelancer_contract_milestones')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('freelancer_contract_deliverable_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverable_id');
            $table->foreign('deliverable_id', 'fc_deliverable_attachments_deliverable_id_fk')
                ->references('id')->on('freelancer_contract_deliverables')->cascadeOnDelete();
            $table->string('disk_path', 255);
            $table->string('original_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_contract_deliverable_attachments');
        Schema::dropIfExists('freelancer_contract_deliverables');
    }
};
