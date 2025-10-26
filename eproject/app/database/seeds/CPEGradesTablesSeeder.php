<?php

class CPEGradesTablesSeeder extends Seeder {

	public function run()
	{
		\DB::table('previous_cpe_grades')->insert(array(
			0 =>
				array(
					'grade'      => 'Non',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
			1 =>
				array(
					'grade'      => 'A',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
			2 =>
				array(
					'grade'      => 'B',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
			3 =>
				array(
					'grade'      => 'C',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
			4 =>
				array(
					'grade'      => 'D',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
            5 =>
                array(
                    'grade'      => 'Unspecified',
                    'created_at' => '2014-09-24 18:08:26.465809',
                    'updated_at' => '2014-09-24 18:08:26.465809',
                )
		));

		\DB::table('current_cpe_grades')->insert(array(
			0 =>
				array(
					'grade'      => 'Non',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
			1 =>
				array(
					'grade'      => 'A',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
			2 =>
				array(
					'grade'      => 'B',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
			3 =>
				array(
					'grade'      => 'C',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
			4 =>
				array(
					'grade'      => 'D',
					'created_at' => '2014-09-24 18:08:26.465809',
					'updated_at' => '2014-09-24 18:08:26.465809',
				),
            5 =>
                array(
                    'grade'      => 'Unspecified',
                    'created_at' => '2014-09-24 18:08:26.465809',
                    'updated_at' => '2014-09-24 18:08:26.465809',
                )
		));
	}
}