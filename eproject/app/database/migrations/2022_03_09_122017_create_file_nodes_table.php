<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileNodesTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('file_nodes', function(Blueprint $table) {
      $table->increments('id');
      $table->integer('parent_id')->nullable()->index();
      $table->integer('lft')->nullable()->index();
      $table->integer('rgt')->nullable()->index();
      $table->integer('depth')->nullable();
      $table->integer('root_id')->nullable()->index();
      $table->integer('priority');
      $table->integer('type');
      $table->integer('version');
      $table->boolean('is_latest_version');
      $table->integer('origin_id')->nullable();
      $table->integer('upload_id')->nullable();
      $table->text('name')->default('');
      $table->text('description')->default('');
      $table->unsignedInteger('created_by');
      $table->unsignedInteger('updated_by');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('parent_id')->references('id')->on('file_nodes')->onDelete('cascade');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
    });

    \DB::statement('CREATE UNIQUE INDEX file_nodes_unique ON file_nodes(origin_id, version) WHERE deleted_at IS NULL');
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::drop('file_nodes');
  }

}
