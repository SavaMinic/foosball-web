<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('team', function ($table) {
			$table->increments('id');
			$table->string('name')->unique();
			$table->integer('games_won');
			$table->integer('games_lost');
			$table->integer('goals_scored');
			$table->integer('goals_conceded');
			$table->text('unique_key')->nullable();
			$table->unique('unique_key');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('team');
	}

}
