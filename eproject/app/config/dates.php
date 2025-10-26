<?php

/*
 * i    Minutes with leading zeros                      - 00 through 59
 * s    Seconds with leading zeros                      - 00 through 59
 * g    Hours (12 hour format) without leading zero     - 1 through 12
 * h    Hours (12 hour format) with leading zeros       - 01 through 12
 * G    Hours (24 hour format) without leading zeros    - 0 through 23
 * H    Hours (24 hour format) with leading zeros       - 00 through 23
 * A    12 hour clock (uppercase)                       - AM/PM
 * a    12 hour clock (lowercase)                       - am/pm
 * l    Day of the week                                 - Saturday
 * d    Date with leading zero                          - 01
 * m    Month with leading zero                         - 01
 * M    Month abbreviated                               - Nov
 * F    Month full                                      - November
 * Y    Year (4 digits)                                 - 2011
 *
 * */
return array(

    // date formatting format for submission dates
    'submission_date_formatting'                    => 'd-M-Y',
    'submitted_at'                                  => 'd-M-Y',
    'reversed_date'                                 => 'Y-m-d',
    'standard'                                      => 'd/m/Y',
    'standard_spaced'                               => ' d / m / Y ',
    'created_and_updated_at_formatting'             => 'd-M-Y g:i A',
    'created_at'                                    => 'd-M-Y g:i A',
    'updated_at'                                    => 'd-M-Y g:i A',
    'readable_timestamp'                            => 'd-M-Y g:i A',
    'readable_timestamp_slash'                      => 'd/M/Y g:i A',
    'time_only'                                     => 'g:i A',
    'timestamp'                                     => 'Y-M-d H:i:s',
    'full_format'                                   => 'd\t\h F Y (l), g.i a',
    'full_format_without_time'                      => 'd\t\h F Y (l)',
    'full_format_without_time_and_day'              => 'd\t\h F Y',
    'day'                                           => 'l',
    'standard_spaced_date_and_day'                  => ' d / m / Y (l)',
    'published_to_post_contract_date_formatting'    => 'd-M-Y (l)',
    // datepicker plugin-specific format
    'date_picker_standard'                          => 'yy-mm-dd',
    'months'                                        => 'M-Y',
    'month'                                         => 'm',
    'year'                                          => 'Y',

);
