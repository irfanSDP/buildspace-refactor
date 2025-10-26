<?php

class Utilities {
    public static function sanitize_file_name($filename)
    {
        $special_chars = array( "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", chr(0) );
        $filename      = preg_replace("#\x{00a0}#siu", ' ', $filename);
        $filename      = str_replace($special_chars, '', $filename);
        $filename      = str_replace(array( '%20', '+' ), '-', $filename);
        $filename      = preg_replace('/[\r\n\t -]+/', '-', $filename);

        return trim($filename, '.-_');
    }

    public static function number_clean($num, Array $options = null)
    {
        // remove zeros from end of number ie. 140.00000 becomes 140.
        $clean = rtrim($num, '0');

        // remove decimal point if an integer ie. 140. becomes 140
        $clean = rtrim($clean, '.');

        // check the length of string behind the separator seperator
        $explodedNum   = explode('.', $clean);
        $decimalLength = ( count($explodedNum) > 1 ) ? strlen($explodedNum[1]) : 0;

        if( ! $options )
        {
            return number_format($num, $decimalLength);
        }
        else
        {
            if( array_key_exists('display_scientific', $options) && array_key_exists('displayFull', $options['display_scientific']) && ! $options['display_scientific']['displayFull'] )
            {
                return self::displayScientific($num, ( array_key_exists('charLength', $options['display_scientific']) ) ? $options['display_scientific']['charLength'] : false, array(
                    'decimal_places'     => $decimalLength,
                    'decimal_points'     => $options['decimal_points'],
                    'thousand_separator' => $options['thousand_separator']
                ), $options['display_scientific']['displayFull']);
            }
            else
            {
                return number_format($num, $decimalLength, $options['decimal_points'], $options['thousand_separator']);
            }
        }
    }

    public static function shiftRightLeftNestedSet($componentName, $first, $delta, $rootId, Doctrine_Connection $con = null)
    {
        $tableClass = Doctrine_Core::getTable($componentName);
        $pdo        = $con ? $con->getDbh() : $tableClass->getConnection()->getDbh();

        // shift left columns
        $stmt = $pdo->prepare("UPDATE " . $tableClass->getTableName() . " set lft = lft + " . $delta . "
        WHERE lft >= " . $first . " AND root_id = " . $rootId . " AND deleted_at IS NULL");

        $stmt->execute();

        // shift right columns
        // shift left columns
        $stmt = $pdo->prepare("UPDATE " . $tableClass->getTableName() . " set rgt = rgt + " . $delta . "
        WHERE rgt >= " . $first . " AND root_id = " . $rootId . " AND deleted_at IS NULL");

        $stmt->execute();
    }

    public static function displayScientific($num, $charLength = false, Array $options = null, $displayFull = false)
    {
        $num = str_replace(',', '', $num);

        $num = number_format($num, $options['decimal_places'], '.', '');

        if( $charLength )
        {
            if( strlen($num) > $charLength && ! $displayFull )
            {
                return '<b>' . sprintf('%.2E', $num) . '</b>';
            }

            return number_format($num, $options['decimal_places'], $options['decimal_points'], $options['thousand_separator']);
        }

        if( $displayFull )
        {
            return number_format($num, $options['decimal_places'], $options['decimal_points'], $options['thousand_separator']);
        }

        return '<b>' . sprintf('%.2E', $num) . '</b>';
    }

    public static function implodeObjects($objectArray, $methodName, $stringToImplodeWith, $bDropDuplicate = false)
    {
        $stringArray      = array();
        $cacheStringArray = array();

        foreach($objectArray as $object)
        {
            if( $bDropDuplicate and in_array($object->{$methodName}(), $cacheStringArray) )
            {
                continue;
            }

            $stringArray[]      = $object->{$methodName}();
            $cacheStringArray[] = $object->{$methodName}();
        }

        if( ! is_null($stringToImplodeWith) )
            return implode($stringToImplodeWith, $stringArray);
        else
            return $stringArray;
    }

    public static function explodeToDate($delimiter, $str)
    {
        $pieces = explode('' . $delimiter . '', $str);

        return count($pieces) == 3 ? $pieces : array( null, null, null );
    }

    public static function explodeToTime($delimiter, $str)
    {
        $pieces = explode('' . $delimiter . '', $str);

        return count($pieces) == 2 ? $pieces : array( null, null );
    }

    public static function partitionArray(Array $list, $p)
    {
        $listlen   = count($list);
        $partlen   = floor($listlen / $p);
        $partrem   = $listlen % $p;
        $partition = array();
        $mark      = 0;
        for($px = 0; $px < $p; $px++)
        {
            $incr             = ( $px < $partrem ) ? $partlen + 1 : $partlen;
            $partition[ $px ] = array_slice($list, $mark, $incr);
            $mark += $incr;
        }

        return $partition;
    }

    public static function newLineToBreak($record)
    {
        $gluedPieces = '';
        if( strlen($record) > 0 )
        {
            $pieces = preg_split('/[\r\n]+/', $record, -1, PREG_SPLIT_NO_EMPTY);

            foreach($pieces as $key => $piece)
            {
                $gluedPieces .= $piece . "<br/>";
            }
        }

        return trim($gluedPieces);
    }

    public static function getDay($day)
    {
        switch($day)
        {
            case Constants::DAY_SUNDAY:
                return Constants::DAY_SUNDAY_TEXT;
            case Constants::DAY_MONDAY:
                return Constants::DAY_MONDAY_TEXT;
            case Constants::DAY_TUESDAY:
                return Constants::DAY_TUESDAY_TEXT;
            case Constants::DAY_WEDNESDAY:
                return Constants::DAY_WEDNESDAY_TEXT;
            case Constants::DAY_THURSDAY:
                return Constants::DAY_THURSDAY_TEXT;
            case Constants::DAY_FRIDAY:
                return Constants::DAY_FRIDAY_TEXT;
            case Constants::DAY_SATURDAY:
                return Constants::DAY_SATURDAY_TEXT;
            default:
                throw new Exception('Invalid Day');
        }
    }

    public static function getAllFormulatedColumnConstants($className)
    {
        $array   = array();
        $reflect = new ReflectionClass($className);
        $prefix  = 'FORMULATED_COLUMN';

        foreach($reflect->getConstants() as $key => $value)
        {
            if( substr($key, 0, strlen($prefix)) == $prefix )
            {
                $array[ $key ] = $value;
            }
        }

        return $array;
    }

    public static function checkPostgresqlMultiplyAggregateFunction()
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        // pg_proc.prokind was added in PostgreSQL 11 only, and
        // pg_proc.proisagg was removed, incompatibly
        $stmt = $pdo->prepare("SELECT EXISTS(SELECT 1 
        FROM information_schema.columns 
        WHERE table_name='pg_proc' and column_name='prokind')");

        $stmt->execute();

        $isPG11 = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        $columnName = 'proisagg IS TRUE';

        if($isPG11)
        {
            $columnName = "prokind = 'a'";//aggregate
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pg_proc WHERE proname = 'multiply' AND ".$columnName);

        $stmt->execute();

        $multiplyExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if( !$multiplyExists )
        {
            $stmt = $pdo->prepare("CREATE FUNCTION multiply(decimal,decimal) returns decimal as $$
            select $1*$2;
            $$ language sql immutable strict");
            
            $stmt->execute();

            $stmt = $pdo->prepare("CREATE AGGREGATE multiply(decimal) (
                sfunc=multiply,
                stype=decimal,
                initcond=1);");
            
            $stmt->execute();
        }
    }

    /*
     * Function to generate excel like column-name of a number
     * 1 = A
     * 2 = B
     * 27 = AA
     * 28 = AB
     */
    public static function generateCharFromNumber($num, $includeIandO = false)
    {
        if( ! $includeIandO )
        {
            $alphabet = array(
                'A', 'B', 'C', 'D', 'E', 'F', 'G',
                'H', 'J', 'K', 'L', 'M', 'N',
                'P', 'Q', 'R', 'S', 'T', 'U',
                'V', 'W', 'X', 'Y', 'Z'
            );

            $charCount = 24;
        }
        else
        {
            $alphabet = array(
                'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
                'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
                'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
            );

            $charCount = 26;
        }

        $numeric = ( $num - 1 ) % $charCount;
        $letter  = $alphabet[ $numeric ];
        $num2    = intval(( $num - 1 ) / $charCount);

        if( $num2 > 0 )
        {
            return Utilities::generateCharFromNumber($num2) . $letter;
        }
        else
        {
            return $letter;
        }
    }

    public static function inlineJustify(String $text, int $width)
    {
        $words = SplFixedArray::fromArray(array_map("trim", explode(" ", trim($text))));
        $wordCount = $words->count() > 1 ? $words->count() - 1 : $words->count();
        $wordLength = strlen(implode("", $words->toArray()));

        if(3*$wordLength < 2*$width)
        {
            // don't touch lines shorter than 2/3 * width
            return $text;
        }

        $spaces = $width - $wordLength;

        $index = 0;

        do
        {
            $words->offsetSet($index, $words->offsetGet($index)."&nbsp;");
            $index = ($index + 1) % $wordCount;
            $spaces--;
        } while ($spaces>0);

        return implode("", $words->toArray());
    }

    public static function justify($text, int $width)
    {
        if(strlen($text) <= $width)
        {
            $lines = new SplFixedArray(1);
            $lines[0] = $text;

            return $lines;
        }

        // lines is an array of lines containing the word-wrapped text
        $lines = SplFixedArray::fromArray(explode("__$%@random#$()__", wordwrap($text, $width, "__$%@random#$()__")));

        if($lines->count() == 1)
            return $lines;//no need to justify if it's just one line paragraph

        foreach($lines as $lineIndex => $line)
        {
            if($lineIndex == $lines->count()-1)
            {
                continue;//no need to justify last line;
            }

            $words = SplFixedArray::fromArray(array_map("trim", explode(" ", trim($line))));
            $wordCount = $words->count() > 1 ? $words->count() - 1 : $words->count();
            $wordLength = strlen(implode("", $words->toArray()));

            if(3*$wordLength < 2*$width)
            {
                // don't touch lines shorter than 2/3 * width
                continue;
            }

            $spaces = $width - $wordLength;

            $index = 0;

            do
            {
                $words->offsetSet($index, $words->offsetGet($index)." ");
                $index = ($index + 1) % $wordCount;
                $spaces--;
            } while ($spaces>0);

            $lines->offsetSet($lineIndex, implode("", $words->toArray()));

            unset($line, $words);
        }

        return $lines;
    }

    public static function justifyHtmlString($text, $width)
    {
        $text = str_replace("&nbsp;", ' ', $text);

        // if the plain text is shorter than the maximum length, return the whole text
        if( strlen(preg_replace('/<.*?>/', '', $text)) <= $width )
        {
            return self::_justifyHtml($text, $width);
        }

        // splits all html-tags to scanable lines
        preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $htmlParsed, PREG_SET_ORDER);

        $lineNo = 0;
        $lines  = array();

        foreach($htmlParsed as $parsed)
        {
            if( array_key_exists($lineNo, $lines) && strlen($lines[ $lineNo ]) > 0 )
            {
                $lines[ $lineNo ] .= ' ' . $parsed[0];
            }
            else
            {
                $lines[ $lineNo ] = $parsed[0];
            }

            if( strtolower($parsed[1]) == '<div>' or strtolower($parsed[1]) == '<br>' or strtolower($parsed[1]) == '<br />' )//div consider as line break thus it should be on the next line
            {
                $lineNo++;
            }
        }

        $finalLines = array();

        foreach($lines as $line)
        {
            $justifiedLines = self::_justifyHtml($line, $width)->toArray();
            $finalLines     = array_merge($finalLines, $justifiedLines);
        }

        $carriedOpenTags = array();

        foreach($finalLines as $key => $finalLine)
        {
            //if line contains ONLY html tags then we remove it from final lines
            if( mb_strlen(strip_tags(html_entity_decode(trim($finalLine))), 'UTF-8') == 0 )
            {
                unset( $finalLines[ $key ] );
                continue;
            }

            preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $finalLine, $result);
            $openedTags = $result[1];
            #put all closed tags into an array
            preg_match_all("#</([a-z]+)>#iU", $finalLine, $result);
            $closedTags = $result[1];

            if( count($openedTags) > count($closedTags) )
            {
                $count    = count($openedTags);
                $moreTags = $openedTags;
                $lessTags = $closedTags;

                $needToClose = true;

                foreach($openedTags as $openedTag)
                {
                    if( ! in_array($openedTag, $closedTags) )
                    {
                        $carriedOpenTags[] = $openedTag;
                    }
                }
            }
            else
            {
                $count    = count($closedTags);
                $moreTags = $closedTags;
                $lessTags = $openedTags;

                $needToClose = false;

                foreach($carriedOpenTags as $idx => $carriedOpenTag)
                {
                    if( in_array($carriedOpenTag, $closedTags) )
                    {
                        unset( $carriedOpenTags[ $idx ] );
                    }
                }
            }

            $moreTags        = array_reverse($moreTags);
            $carriedOpenTags = array_reverse($carriedOpenTags);

            for($i = 0; $i < $count; $i++)
            {
                if( ! in_array($moreTags[ $i ], $lessTags) )
                {
                    if( $needToClose )
                        $finalLine .= "</" . $moreTags[ $i ] . ">";
                    else
                        $finalLine = "<" . $moreTags[ $i ] . ">" . $finalLine;
                }
                else
                {
                    unset ( $lessTags[ array_search($moreTags[ $i ], $lessTags) ] );
                }
            }

            foreach($carriedOpenTags as $carriedOpenTag)
            {
                $finalLine = '<' . $carriedOpenTag . '>' . $finalLine . '</' . $carriedOpenTag . '>';
            }

            $finalLines[ $key ] = $finalLine;
        }

        return SplFixedArray::fromArray($finalLines);
    }

    private static function _justifyHtml($text, $width)
    {
        $marker = "__$%@random#$()__";

        // lines is an array of lines containing the word-wrapped text
        $wrapped = self::htmlWrap($text, $width, $marker);
        $lines   = explode($marker, $wrapped);

        $lines = SplFixedArray::fromArray($lines);

        if( $lines->count() == 1 )
        {
            $lines = $lines->toArray();
            //remove any div since it will mess us row height in html table
            $lines[0] = str_ireplace("<div>", "", $lines[0]);
            $lines[0] = str_ireplace("</div>", "", $lines[0]);
            $lines[0] = str_ireplace("<br>", "", $lines[0]);
            $lines[0] = str_ireplace("</br>", "", $lines[0]);
            $lines[0] = str_ireplace("<br />", "", $lines[0]);

            return SplFixedArray::fromArray($lines);//no need to justify if it's just one line paragraph
        }


        foreach($lines as $lineIndex => $line)
        {
            //remove any div since it will mess us row height in html table
            $line = str_ireplace("<div>", "", $line);
            $line = str_ireplace("</div>", "", $line);
            $line = str_ireplace("<br>", "", $line);
            $line = str_ireplace("</br>", "", $line);
            $line = str_ireplace("<br />", "", $line);

            if( $lineIndex == $lines->count() - 1 )
            {
                $lines->offsetSet($lineIndex, $line);
                continue;//no need to justify last line;
            }

            $line = trim($line);

            $words      = explode(" ", $line);
            $words      = array_map("trim", $words);
            $wordCount  = count($words) > 1 ? count($words) - 1 : count($words);
            $wordLength = mb_strlen(strip_tags(html_entity_decode(implode("", $words))), 'auto');

            if( 3 * $wordLength < 2 * $width )
            {
                // don't touch lines shorter than 2/3 * width
                continue;
            }

            $spaces = $width - $wordLength;

            $index = 0;
            do
            {
                $words[ $index ] = $words[ $index ] . " ";
                $index           = ( $index + 1 ) % $wordCount;
                $spaces--;
            }
            while( $spaces > 0 );

            $lines->offsetSet($lineIndex, implode("", $words));

            unset( $line );
        }

        return $lines;
    }

    public static function htmlWrap(&$str, $maxLength, $char = "\n")
    {
        $count   = 0;
        $newStr  = '';
        $openTag = false;
        $lenstr  = strlen($str);
        $lastspace = 0;
        
        for($i = 0; $i < $lenstr; $i++)
        {
            $newStr .= $str{$i};
            if( $str{$i} == '<' )
            {
                $openTag = true;
                continue;
            }

            if( ( $openTag ) && ( $str{$i} == '>' ) )
            {
                $openTag = false;
                continue;
            }

            if( ! $openTag )
            {
                if( $str{$i} == ' ' )
                {
                    if( $count == 0 )
                    {
                        $newStr = substr($newStr, 0, -1);
                        continue;
                    }
                    else
                    {
                        $lastspace = $count + 1;
                    }
                }
                $count++;
                if( $count == $maxLength )
                {
                    if( isset( $str{$i + 1} ) && $str{$i + 1} != ' ' && $lastspace && ( $lastspace < $count ) )
                    {
                        $tmp    = ( $count - $lastspace ) * -1;
                        $newStr = substr($newStr, 0, $tmp) . $char . substr($newStr, $tmp);
                        $count  = $tmp * -1;
                    }
                    else
                    {
                        $newStr .= $char;
                        $count = 0;
                    }
                    $lastspace = 0;
                }
            }
        }

        return $newStr;
    }

    /**
     * Get all values from specific key in a multidimensional array
     *
     * @param $key string
     * @param $arr array
     *
     * @return null|string|array
     */
    public static function arrayValueRecursive($key, array $arr)
    {
        $val = array();
        array_walk_recursive($arr, function ($v, $k) use ($key, &$val)
        {
            if( $k == $key ) array_push($val, $v);
        });

        return count($val) > 1 ? $val : array( array_pop($val) );
    }

    public static function getTimezones()
    {
        $zonesArray = array();
        $timestamp = time();
        foreach(timezone_identifiers_list() as $key => $zone)
        {
            date_default_timezone_set($zone);
            $zonesArray[$key]['id'] = $zone;
            $zonesArray[$key]['name'] =  $zone.' - '.'UTC/GMT ' . date('P', $timestamp);

        }

        return array(
            'identifier' => 'id',
            'items'      => $zonesArray,
        );
    }

    public static function generateRFQReferenceNo($type, $rfqCount)
    {
        if( $type == RFQ::TYPE_PROJECT )
        {
            $rfqCount = sprintf(sfConfig::get('app_rfq_project_ref_no_zero_fill_length'), $rfqCount);
        }
        else
        {
            $rfqCount = sprintf(sfConfig::get('app_rfq_resource_ref_no_zero_fill_length'), $rfqCount);
        }

        return $rfqCount;
    }

    public static function generatePurchaseOrderReferenceNo($poCount)
    {
        return sprintf(sfConfig::get('app_rfq_project_ref_no_zero_fill_length'), $poCount);
    }

    public static function percent($num_amount, $num_total)
    {
        $count1 = ( $num_total == 0 ) ? 0 : $num_amount / $num_total;

        return $count1 * 100;
    }

    public static function divide($dividend, $divisor)
    {
        if( $divisor == 0 ) return 0;

        return $dividend / $divisor;
    }

    public static function prelimRounding($value)
    {
        return number_format($value, 2, '.', '');
    }

    public static function array_recursive_search($array, $key, $value)
    {
        $results = array();

        if( is_array($array) )
        {
            if( isset( $array[ $key ] ) && $array[ $key ] == $value )
            {
                $results[] = $array;
            }

            foreach($array as $subarray)
            {
                $results = array_merge($results, self::array_recursive_search($subarray, $key, $value));
            }
        }

        return $results;
    }

    public static function delTree($dir)
    {
        $files = array_diff(scandir($dir), array( '.', '..' ));

        foreach($files as $file)
        {
            ( is_dir("$dir/$file") ) ? null : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    public static function createDateRangeArray($strDateFrom, $strDateTo)
    {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.

        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo   = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if( $iDateTo >= $iDateFrom )
        {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
            while( $iDateFrom < $iDateTo )
            {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }

        return $aryRange;
    }

    public static function getCalendarEventsByProject(ProjectStructure $project, $eventType = null)
    {
        $dates = new SplFixedArray();

        if( $project->type != ProjectStructure::TYPE_ROOT )
            return $dates;

        if( $eventType != GlobalCalendar::TYPE_NON_HOLIDAY )
        {
            $globalCalendarPublicEvents = DoctrineQuery::create()->select('e.start_date, e.end_date')
                ->from('GlobalCalendar e')
                ->where('e.region_id = ?', $project->MainInformation->region_id)
                ->andWhere('e.event_type = ?', GlobalCalendar::EVENT_TYPE_PUBLIC)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)->execute();

            foreach($globalCalendarPublicEvents as $event)
            {
                $dateRange = Utilities::createDateRangeArray($event['start_date'], $event['end_date']);

                foreach($dateRange as $date)
                {
                    if( ! in_array($date, $dates->toArray()) )
                    {
                        $dates->setSize($dates->getSize() + 1);
                        $dates[ $dates->getSize() - 1 ] = $date;
                    }
                }
            }

            $globalCalendarStateEvents = DoctrineQuery::create()->select('e.start_date, e.end_date')
                ->from('GlobalCalendar e')
                ->where('e.region_id = ?', $project->MainInformation->region_id)
                ->andWhere('e.subregion_id = ?', $project->MainInformation->subregion_id)
                ->andWhere('e.event_type = ?', GlobalCalendar::EVENT_TYPE_STATE)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)->execute();

            foreach($globalCalendarStateEvents as $event)
            {
                $dateRange = Utilities::createDateRangeArray($event['start_date'], $event['end_date']);

                foreach($dateRange as $date)
                {
                    if( ! in_array($date, $dates->toArray()) )
                    {
                        $dates->setSize($dates->getSize() + 1);
                        $dates[ $dates->getSize() - 1 ] = $date;
                    }
                }
            }
        }

        $globalCalendarOtherEventsQuery = DoctrineQuery::create()->select('e.start_date, e.end_date')
            ->from('GlobalCalendar e')
            ->where('e.region_id = ?', $project->MainInformation->region_id)
            ->andWhere('e.event_type = ?', GlobalCalendar::EVENT_TYPE_OTHER);

        switch($eventType)
        {
            case GlobalCalendar::TYPE_HOLIDAY:
                $globalCalendarOtherEventsQuery->andWhere('e.is_holiday IS TRUE');
                break;
            case GlobalCalendar::TYPE_NON_HOLIDAY:
                $globalCalendarOtherEventsQuery->andWhere('e.is_holiday IS FALSE');
                break;
            default:
                break;
        }

        $globalCalendarOtherEvents = $globalCalendarOtherEventsQuery->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)->execute();

        foreach($globalCalendarOtherEvents as $event)
        {
            $dateRange = Utilities::createDateRangeArray($event['start_date'], $event['end_date']);

            foreach($dateRange as $date)
            {
                if( ! in_array($date, $dates->toArray()) )
                {
                    $dates->setSize($dates->getSize() + 1);
                    $dates[ $dates->getSize() - 1 ] = $date;
                }
            }
        }

        $projectCalendarEventsQuery = DoctrineQuery::create()->select('e.start_date, e.end_date')
            ->from('ProjectManagementCalendar e')
            ->where('e.project_structure_id = ?', $project->id);

        switch($eventType)
        {
            case GlobalCalendar::TYPE_HOLIDAY:
                $projectCalendarEventsQuery->andWhere('e.is_holiday IS TRUE');
                break;
            case GlobalCalendar::TYPE_NON_HOLIDAY:
                $projectCalendarEventsQuery->andWhere('e.is_holiday IS FALSE');
                break;
            default:
                break;
        }

        $projectCalendarEvents = $projectCalendarEventsQuery->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)->execute();

        foreach($projectCalendarEvents as $event)
        {
            $dateRange = Utilities::createDateRangeArray($event['start_date'], $event['end_date']);

            foreach($dateRange as $date)
            {
                if( ! in_array($date, $dates->toArray()) )
                {
                    $dates->setSize($dates->getSize() + 1);
                    $dates[ $dates->getSize() - 1 ] = $date;
                }
            }
        }

        return $dates;
    }

    public static function getNonWorkingDaysFromDateRange($fromDate, $toDate, SplFixedArray $nonWorkingDays, $excludeSaturdays = true, $excludeSundays = true)
    {
        $dateRange = Utilities::createDateRangeArray($fromDate, $toDate);

        foreach($dateRange as $date)
        {
            $day = date('N', strtotime($date));

            if( ! in_array($date, $nonWorkingDays->toArray()) && ( $day == 6 or $day == 7 ) )
            {
                if( ( $excludeSaturdays && $day == 6 ) or ( $excludeSundays && $day == 7 ) )
                {
                    $nonWorkingDays->setSize($nonWorkingDays->getSize() + 1);
                    $nonWorkingDays[ $nonWorkingDays->getSize() - 1 ] = $date;
                }
            }
        }

        return $nonWorkingDays;
    }

    public static function distanceInWorkingDays($fromDate, $toDate, SplFixedArray $nonWorkingDays, $excludeSaturdays = true, $excludeSundays = true)
    {
        $pos  = $fromDate;
        $days = 0;

        $nonWorkingDays = Utilities::getNonWorkingDaysFromDateRange($fromDate, $toDate, $nonWorkingDays, $excludeSaturdays, $excludeSundays);

        while( strtotime($pos) <= strtotime($toDate) )
        {
            $days += ( Utilities::isNonWorkingDay($pos, $nonWorkingDays, $excludeSaturdays, $excludeSundays) ) ? 0 : 1;
            $pos = date('Y-m-d', strtotime("+1 day", strtotime($pos)));
        }

        return $days;
    }

    public static function distanceFromDateToDate($fromDate, $toDate, $excludeSaturdays = true, $excludeSundays = true)
    {
        $pos  = $fromDate;
        $days = 0;

        while( strtotime($pos) <= strtotime($toDate) )
        {
            $day = date('N', strtotime($pos));
            $days += ( ( $excludeSaturdays && $day == 6 ) or ( $excludeSundays && $day == 7 ) ) ? 0 : 1;
            $pos = date('Y-m-d', strtotime("+1 day", strtotime($pos)));
        }

        return $days;
    }

    public static function isNonWorkingDay($date, SplFixedArray $nonWorkingDays, $excludeSaturdays = true, $excludeSundays = true)
    {
        $day = date('N', strtotime($date));

        return ( $day == 6 && $excludeSaturdays ) || ( $day == 7 && $excludeSundays ) || in_array($date, $nonWorkingDays->toArray());
    }

    public static function computeWorkingDate($date, SplFixedArray $nonWorkingDays, $excludeSaturdays = true, $excludeSundays = true)
    {
        while( Utilities::isNonWorkingDay($date, $nonWorkingDays, $excludeSaturdays, $excludeSundays) )
        {
            $date = date('Y-m-d', strtotime("+1 day", strtotime($date)));
        }

        return $date;
    }

    public static function computeEndDateByDuration($startDate, $duration, SplFixedArray $nonWorkingDays, $excludeSaturdays = true, $excludeSundays = true)
    {
        $date = $startDate;
        $q    = $duration - 1;
        while( $q > 0 )
        {
            $date = date('Y-m-d', strtotime("+1 day", strtotime($date)));

            if( ! Utilities::isNonWorkingDay($date, $nonWorkingDays, $excludeSaturdays, $excludeSundays) )
                $q--;
        }

        return $date;
    }

    public static function generateStockOutNo($count)
    {
        return sprintf(sfConfig::get('app_stock_out_ref_no_zero_fill_length'), $count);
    }

    public static function truncateString($string, $limiter, $continueString = '...')
    {
        return ( strlen($string) > $limiter ) ? substr($string, 0, $limiter) . $continueString : $string;
    }

    public static function mimeContentType($filename)
    {
        $result = new finfo();

        if( is_resource($result) === true )
        {
            return $result->file($filename, FILEINFO_MIME_TYPE);
        }

        return false;
    }

    public static function massageText($txt, $toLower = true)
    {
        // remove front and back spaces
        $txt = trim($txt);

        // change to lowercase
        if( $toLower ) $txt = strtolower($txt);

        // adding - for spaces and union characters
        $find = array( ' ', '&', '\r\n', '\n', '+', ',' );
        $txt  = str_replace($find, '-', $txt);

        //delete and replace rest of special chars
        $find = array( '/[^a-zA-Z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/' );
        $repl = array( '', '-', '' );
        $txt  = preg_replace($find, $repl, $txt);

        return $txt;
    }

    /**
     * Filters an array for only integer values.
     *
     * @param $array
     *
     * @return array
     */
    public static function array_filter_integer($array)
    {
        if( empty( $array ) ) return array();

        return array_filter($array, function($var)
        {
            // Check for integer to only allow integer values.
            // Check for numeric because intval(string) returns 0.
            return is_integer(intval($var)) && is_numeric($var);
        });
    }

    public static function createFilePath(array $folderNames, $filename, $extension, $overwrite = false)
    {
        $path = '';

        foreach($folderNames as $folderName)
        {
            $path .= $folderName.DIRECTORY_SEPARATOR;
        }

        if( ! is_dir($path) ) mkdir($path, 0775, true);

        $newFilename = $filename;

        if( ! empty( $extension ) ) $extension = ".{$extension}";

        if( ! $overwrite )
        {
            $counter     = 0;
            while( file_exists($path . $newFilename . $extension) )
            {
                $counter++;
                $newFilename = "{$filename} ({$counter})";
            }
        }

        return $path . $newFilename . $extension;
    }

    public static function setAttributeAsKey(array $items, $keyName, $unsetKey = true)
    {
        $output = array();

        foreach($items as $key => $item)
        {
            $output[$item[$keyName]] = $item;

            if($unsetKey) unset($output[$item[$keyName]][$keyName]);

            unset($items[$key]);
        }

        return $output;
    }

    public static function getKeyPairFromAttributes(array $items, $keyAttribute, $valueAttribute)
    {
        $output = array();

        foreach($items as $key => $item)
        {
            $output[$item[$keyAttribute]] = $item[$valueAttribute];

            unset($items[$key]);
        }

        return $output;
    }

    /**
    * A chunking function for SplFixedArray
    *
    * Operates the same as array_chunk() but without $preserve_keys, for obvious reasons.
    *
    * @param SplFixedArray $arr
    * @param int $size
    *
    * @return SplFixedArray[]
    */
    public static function arrayChunkSPL(SplFixedArray $arr, $size)
    {
        // Determine the number of chunks that need to be created
        $chunks = new SplFixedArray(ceil(count($arr) / $size));
        foreach ($arr as $idx => $value)
        {
            if ($idx % $size === 0)
            {
                // Create a new chunk every time we reach the maximum size
                $chunks[$idx / $size] = $chunk = new SplFixedArray($size);
            }
            // Add to the current chunk
            $chunk[$idx % $size] = $value;
        }
        // Reduce the size of the final chunk to match remainder
        $sizeOfLastChunk = count($arr) % $size;

        if($sizeOfLastChunk == 0)$sizeOfLastChunk = $size;

        $chunk->setSize($sizeOfLastChunk);
        
        return $chunks;
    }

    public static function SplFixedArrayPush(SplFixedArray &$arr, $item)
    {
        $latestSize = $arr->getSize() + 1;

        $arr->setSize($latestSize);

        $arr[$latestSize -1] = $item;
    }

    /**
     * Strip away unwanted characters for xml.
     * Reference: https://stackoverflow.com/questions/12229572/php-generated-xml-shows-invalid-char-value-27-message
     *
     * @param $string
     *
     * @return mixed
     */
    public static function utf8_for_xml($string)
    {
        return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
    }

    /**
     * @param      $string
     * @param null $format
     *
     * @return int|string
     */
    public static function convertJavascriptDateToPhp($string, $format = null)
    {
        $dateTime = DateTime::createFromFormat('D M d Y H:i:s T +', $string);

        if( ! $format ) return $dateTime;

        return $dateTime->format($format);
    }

    /**
     * @param $percentages
     */
    public static function normalizeTotalPercentage($percentages)
    {
        foreach($percentages as $key => $percentage)
        {
            $percentages[$key] = round($percentage, 2);
        }

        $sum = array_sum(array_map(function($perc) {
            return round($perc, 2);
        }, $percentages));

        $variance = (float) number_format(100.0 - $sum, 2);

        end($percentages);
        $lastIndex = key($percentages);
        $percentages[$lastIndex] = ($variance > 0.0) ? $percentages[$lastIndex] + abs($variance) : $percentages[$lastIndex] - abs($variance);

        return $percentages;
    }

    public static function checkLicenseValidity()
    {
        $client = new GuzzleHttp\Client(array(
            'debug'    => false,
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res            = $client->post("buildspace/checkLicenseValidity");

            $content        = $res->getBody()->getContents();
            $jsonObj        = json_decode($content);
            $isLicenseValid = ($jsonObj) ? $jsonObj->isLicenseValid :  false;
        }
        catch(Exception $e)
        {
            throw $e;
        }

        return $isLicenseValid;
    }

    public static function getRoundedAmount($eProjectOriginId, $amount)
    {
        if(empty($eProjectOriginId))
        {
            return round($amount, 2);
        }

        $client = new GuzzleHttp\Client(array(
            'debug'    => false,
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res = $client->post('buildspace/getRoundedAmount', [
                'form_params' => [
                    'eProjectOriginId' => $eProjectOriginId,
                    'amount'           => $amount, 
                ]
            ]);

            $content       = $res->getBody()->getContents();
            $jsonObj       = json_decode($content);
            $roundedAmount = $jsonObj->roundedAmount;
        }
        catch(Exception $e)
        {
            throw $e;
        }

        return $roundedAmount;
    }

    public static function getEProjectArtisanPath()
    {
        $directory = sfConfig::get('app_eproject_shared_folder');

        $dirArray = explode('/', $directory);

        $eprojectRootDirArray = [];
        for($x = 0; $x < (count($dirArray) - 3); $x++)
        {
            $eprojectRootDirArray[] = $dirArray[$x];
        }

        $eprojectRootDir = implode('/', $eprojectRootDirArray);

        return $eprojectRootDir.DIRECTORY_SEPARATOR."artisan";
    }

    public static function bulkUpdateSqlGenerator($params, $tableName, $byField)
    {
        $str = 'UPDATE '.$tableName.' SET ';
        $ids = [];
        $row = [];

        foreach ($params as $fieldName => $param)
        {
            $rowStr = $fieldName.' = (CASE '.$byField.' ';
            $cel = [];

            foreach ($param as $id => $value)
            {
                $cel[] = 'WHEN ' . $id . ' THEN ' . $value;
                if(!in_array($id, $ids)){
                    $ids[] = $id;
                }
            }
            
            $rowStr .= implode(' ', $cel);
            $rowStr .= ' ELSE ' . $fieldName . ' END)';
            $row[] = $rowStr;
        }

        $str .= implode(', ', $row);
        $str .= ' WHERE '.$byField.' IN (' . implode(', ', $ids) . ')';

        return $str;
    }
}