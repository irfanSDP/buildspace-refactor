<?php

use PCK\Clauses\Clause;
use PCK\Contracts\Contract;

class ClausesTableSeeder_addIndonesiaCivilContractClauses extends Seeder {

    public function run()
    {
        $contract = Contract::findByType(Contract::TYPE_INDONESIA_CIVIL_CONTRACT);

        $extensionOfTimeClause = Clause::firstOrCreate(array(
            'contract_id' => $contract->id,
            'type'        => Clause::TYPE_EXTENSION_OF_TIME,
            'name'        => Clause::TYPE_EXTENSION_OF_TIME_TEXT,
        ));

        $lossAndExpensesClause = Clause::firstOrCreate(array(
            'contract_id' => $contract->id,
            'type'        => Clause::TYPE_LOSS_AND_EXPENSES,
            'name'        => Clause::TYPE_LOSS_AND_EXPENSES_TEXT,
        ));

        \DB::table('clause_items')->insert(array(
            0  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'a',
                'description' => 'PPK mengubah jadwal yang dapat mempengaruhi pelaksanaan pekerjaan (64.1.a).',
                'priority'    => 0,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            1  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'b',
                'description' => 'Keterlambatan pembayaran kepada penyedia (64.1.b).',
                'priority'    => 1,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            2  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'c',
                'description' => 'PPK tidak memberikan gambar-gambar, spesifikasi dan/atau instruksi sesuai jadwal yang dibutuhkan (64.1.c).',
                'priority'    => 2,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            3  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'd',
                'description' => 'Penyedia belurn bisa masuk ke lokasi sesuai jadwal dalam kontrak (64.1.d).',
                'priority'    => 3,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            4  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'e',
                'description' => 'PPK menginstruksikan kepada pihak penyedia untuk melakukan pengujian tambahan yang setelah dilaksanakan pengujian ternyata tidak ditemukan kerusakan/kegagalan/penyimpangan (64.1.e).',
                'priority'    => 4,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            5  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'f',
                'description' => 'Kerusakan/kegagalan/penyimpangan (64.1.f).',
                'priority'    => 5,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            6  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'g',
                'description' => 'PPK memerintahkan penundaan pelaksanaan pekerjaan (64.1.g)',
                'priority'    => 6,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            7  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'h',
                'description' => 'PPK memerintahkan untuk mengatasi kondisi terntentu yang tidak dapat diduga sebelumnya dan disebabkan oleh PPK (64.1.h).',
                'priority'    => 7,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            8  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'i',
                'description' => 'Ketentuan lain dalan SSKK (64.1.i).',
                'priority'    => 8,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            9  => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'j',
                'description' => 'Pekerjaan tambah (39.1.a).',
                'priority'    => 9,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            10 => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'k',
                'description' => 'Perubahan desain (39.1.b).',
                'priority'    => 10,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            11 => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'l',
                'description' => 'Keterlambatan yang disebabkan oleh PPK (39.1.c).',
                'priority'    => 11,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            12 => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'm',
                'description' => 'Masalah yang timbul diluar kendali penyedia (39.1.d).',
                'priority'    => 12,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            13 => array(
                'clause_id'   => $extensionOfTimeClause->id,
                'no'          => 'n',
                'description' => 'Keadaan kahar (39.1.e)',
                'priority'    => 13,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
        ));

        \DB::table('clause_items')->insert(array(
            0 => array(
                'clause_id'   => $lossAndExpensesClause->id,
                'no'          => 'a',
                'description' => 'PPK mengubah jadwal yang dapat mempengaruhi pelaksanaan pekerjaan (64.1.a).',
                'priority'    => 0,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            1 => array(
                'clause_id'   => $lossAndExpensesClause->id,
                'no'          => 'b',
                'description' => 'Keterlambatan pembayaran kepada penyedia (64.1.b).',
                'priority'    => 1,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            2 => array(
                'clause_id'   => $lossAndExpensesClause->id,
                'no'          => 'c',
                'description' => 'PPK tidak memberikan gambar-gambar, spesifikasi dan/atau instruksi sesuai jadwal yang dibutuhkan (64.1.c).',
                'priority'    => 2,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            3 => array(
                'clause_id'   => $lossAndExpensesClause->id,
                'no'          => 'd',
                'description' => 'Penyedia belurn bisa masuk ke lokasi sesuai jadwal dalam kontrak (64.1.d).',
                'priority'    => 3,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            4 => array(
                'clause_id'   => $lossAndExpensesClause->id,
                'no'          => 'e',
                'description' => 'PPK menginstruksikan kepada pihak penyedia untuk melakukan pengujian tambahan yang setelah dilaksanakan pengujian ternyata tidak ditemukan kerusakan/kegagalan/penyimpangan (64.1.e).',
                'priority'    => 4,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            5 => array(
                'clause_id'   => $lossAndExpensesClause->id,
                'no'          => 'f',
                'description' => 'Kerusakan/kegagalan/penyimpangan (64.1.f).',
                'priority'    => 5,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            6 => array(
                'clause_id'   => $lossAndExpensesClause->id,
                'no'          => 'g',
                'description' => 'PPK memerintahkan penundaan pelaksanaan pekerjaan (64.1.g)',
                'priority'    => 6,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            7 => array(
                'clause_id'   => $lossAndExpensesClause->id,
                'no'          => 'h',
                'description' => 'PPK memerintahkan untuk mengatasi kondisi terntentu yang tidak dapat diduga sebelumnya dan disebabkan oleh PPK (64.1.h).',
                'priority'    => 7,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
            8 => array(
                'clause_id'   => $lossAndExpensesClause->id,
                'no'          => 'i',
                'description' => 'Ketentuan lain dalan SSKK (64.1.i).',
                'priority'    => 8,
                'created_at'  => 'NOW()',
                'updated_at'  => 'NOW()',
            ),
        ));
    }
}