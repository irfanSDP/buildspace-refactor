<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeightedNodesTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('weighted_nodes', function(Blueprint $table) {
      $table->increments('id');
      $table->integer('parent_id')->nullable()->index();
      $table->integer('lft')->nullable()->index();
      $table->integer('rgt')->nullable()->index();
      $table->integer('depth')->nullable();

      $table->string('name', 255)->default('');
      $table->decimal('weight', 5, 2)->default(0);
      $table->unsignedInteger('root_id')->nullable()->index();
      $table->unsignedInteger('priority');
      // $table->integer('reference_id')->nullable()->index();
      // $table->integer('origin_id')->nullable()->index();

      // CreateTenderDocumentFoldersTable

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::drop('weighted_nodes');
  }

}
