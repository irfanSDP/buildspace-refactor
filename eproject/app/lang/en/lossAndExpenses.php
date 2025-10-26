<?php

return array(
    'issueNew'                            => 'Issue New LE',
    'lossAndExpenses'                     => 'Loss and Expenses',
    'le'                                  => 'LE',
    'dateIssued'                          => 'Date Issued',
    'claimAmountProposed'                 => 'Proposed (:currencyCode)',
    'claimAmountGranted'                  => 'Granted (:currencyCode)',
    'status'                              => 'Status',
    'issue'                               => 'Issue',
    'lossAndExpensesReference'            => 'Loss and Expenses Reference',
    'clausesThatEmpower'                  => 'Clause(s) that empower the issuance of LE',
    'deadlineToComply'                    => 'Deadline To Comply',
    'attachments'                         => 'Attachment(s)',
    'leAdded'                             => 'Loss and Expense (:reference) added',
    'leUpdated'                           => 'Loss and Expense (:reference) updated',
    'leDeleted'                           => 'Loss and Expense (:reference) deleted',
    'unreadEmailMessage'                  => 'You have one unread <strong>Loss and Expense</strong> message from ":senderName"',
    'unreadSystemMessage'                 => 'You have one unread Loss and Expense message from ":senderName"',
    'viewCurrentLossAndExpenses'          => 'View Current LE',
    'lossAndExpensesInformation'          => 'LE Information',
    'workflow'                            => 'Workflow',
    'responseForm'                        => 'Response Form',
    'subject'                             => 'Subject',
    'details'                             => 'Details',
    'reason'                              => 'Reason',
    'response'                            => 'Response',
    'responseSubmitted'                   => 'Response Submitted',
    'notAssociatedWithAnyLossAndExpenses' => 'Not associated with any LE',
    'earlyWarnings'                       => 'Early Warnings',
    'architectInstruction'                => 'Architect Instruction',
    'claimAmount'                         => 'Claim Amount',
    'decision'                            => 'Decision',
    'type_agreeOnProposedValue'           => 'Menyetujui jumlah ganti rugi yang diminta oleh Penyedia',
    'type_rejectProposedValue'            => 'Permintaan Penyedia ditolak',
    'type_grant'                          => 'Membayar ganti rugi sejumlah (:currencyCode)',
    'indonesiaCivilContract'              => array(
        'workflowSteps' => array(
            'step1'     => array(
                'main' => 'Penyedia telah mengajukan aplikasi untuk ganti rugi sejumlah :currencyCode :claimAmount, pada :submissionDate.',
            ),
            'step2'     => array(
                'main'         => 'Pembayaran ganti rugi dan kompensasi dilakukan oleh PPK, apabila penyedia telah mengajukan tagihan disertai perhitungnan dan data-data. (Ayat 66.3.g)',
                'decisionNote' => array(
                    'agreeOnProposedValue' => 'PPK telah menetapkan ada tidaknya ganti rugi dan jumlahnya pada :submissionDate. PPK menetapkan ganti rugi sejumlah :currencyCode :claimAmount untuk Penyedia.',
                    'rejectProposedValue'  => 'PPK telah menetapkan ada tidaknya ganti rugi dan jumlahnya pada :submissionDate. Permintaan penyedia ditolak.',
                    'grant'                => 'PPK telah menetapkan ada tidaknya ganti rugi dan jumlahnya pada :submissionDate. PPK menetapkan ganti rugi sejumlah :currencyCode :claimAmount untuk Penyedia.',
                ),
                'responseNote' => 'Penyedia Kontraktor mengajukan banding.',
            ),
            'replyHere' => 'Balas Disini'
        ),
        'compensation'  => '
<strong>Kompensasi</strong>
<br/>
Jika Peristiwa Kompensasi mengakibatkan pengeluaran tambahan dan/atau keterlambatan penyelsaian pekerjaan maka PPk berkewajiban untuk membayar ganti rugi dan/atau memberikan perpanjangan waktu penyelesaian pekerjaan (Ayat 64.2).
<br/>
<br/>
Ganti rugi hanya dapat dibayarkan jika berdasarkan data penunjang dan perhitungan kompensasi yang diajukan oleh Penyedia kepada PPK, dapat dibuktikan kerugian nyata akibat Peristiwa Kompensasi (Ayat 64.3).
<br/>
<br/>
Penyedia tidak berhak atas ganti rugi dan/atau perpanjangan waktu penyelesaian pekerjaan jika penyedia gagal atau lalai untuk memberikan peringatan dini dalam mengantisipasi atau mengatasi dampak Peristiwa Kompensasi (Ayat 64.5)
<br/>
<br/>
Peristiwa Kompensasi yang dapat diberikan kepada penyedia yaitu (Ayat 64.1):
<ol>
    <li>PPK mengubah jadwal yang dapat mempengaruhi pelaksanaan pekerjaan;</li>
    <li>keterlambatan pembayaran kepada penyedia;</li>
    <li>PPK tidak memberikan gambar-gambar, spesifikasi dan/atau instruksi sesuai jadwal yang ditubuhkan;</li>
    <li>Penyedia belurn bisa masuk ke lokasi sesuai jadwal dalam kontrak;</li>
    <li>PPK menginstruksikan kepada pihak penyedia untuk melakukan pengujian tambahan yang setelah dilaksanakan pengujian ternyata tidak ditemukan kerusakan/kegagalan/penyimpangan;</li>
    <li>kerusakan/kegagalan/penyimpangan,</li>
    <li>PPK mernerintahkan penundaan pelaksanaan pekerjaan;</li>
    <li>PPK mernerintahkan untuk mengatasi kondisi tertentu yang tidak dapat diduga sebelumnya dan disebabkan oleh PPK;</li>
    <li>ketentuan lain dalam SSKK.</li>
</ol>
<br/>
<strong>Peringatan Dini (*Kondisi preseden)</strong>
<br/>
Penyedia tidak berhak atas ganti rugi dan/atau perpanjangan waktu penyelesaian pekerjaan jika penyedia gagal atau lalai untuk memberikan peringatan dini dalam mengatisipasi atau mengatasi dampak Peristiwa Kompensasi (Ayat 64.5).
        ',
    ),
);