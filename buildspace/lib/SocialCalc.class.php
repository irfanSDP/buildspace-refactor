<?php

class SocialCalc
{


	public static $defaultFormatp = '#,##0.0%';
	public static $defaultFormatc = '[$$]#,##0.00';
	public static $defaultFormatdt = 'd-mmm-yyyy h:mm:ss';
	public static $defaultFormatd = 'd-mmm-yyyy';
	public static $defaultFormatt = '[h]:mm:ss';
	public static $defaultDisplayTRUE = 'TRUE';
	public static $defaultDisplayFALSE = 'FALSE';

	public static function parseSheet($sData)
	{
		$lines = explode("\n", $sData);
		$sheet = array();
		foreach ($lines as $line) {
			$names = explode(':', $line);


			switch ($names[0]) {
				case 'cell':
					$coord = $names[1];
					$cell = isset($sheet['cells'][$coord]) ? $sheet['cells'][$coord] : array('pos' => SocialCalc::coordToCr($coord));
					$cell += SocialCalc::parseCell($names, 2);
					$sheet['cells'][$coord] = $cell;
					break;

				case "col":
					$coord = $names[1];
					$pos = SocialCalc::coordToCr($coord . '1'); // convert to col number
					$j = 2;
					while ($t = @$names[$j++]) {
						switch ($t) {
							case "w":
								$sheet['colattribs']['width'][$pos[0]] = strval($names[$j++]); // must be text - could be auto or %, etc.
								break;
							case "hide":
								$sheet['colattribs']['hide'][$pos[0]] = $names[$j++];
								break;
						}
					}
					break;

				case "layout":
					preg_match('/^layout\:(\d+)\:(.+)$/', $line, $names); // layouts can have ":" in them
					$sheet['layouts'][intval($names[1])] = $names[2];
					$sheet['layouthash'][$names[2]] = intval($names[1]);
					break;

				case "sheet":
					array_shift($names);
					$sheet['sheets'] = implode(':', $names);
					break;

				case "font":
					$sheet['fonts'][intval($names[1])] = $names[2];
					$sheet['fonthash'][$names[2]] = intval($names[1]);
					break;

				case "color":
					$sheet['colors'][intval($names[1])] = $names[2];
					$sheet['colorhash'][$names[2]] = intval($names[1]);
					break;

				case "border":
					$sheet['borderstyles'][intval($names[1])] = $names[2];
					$sheet['borderstylehash'][$names[2]] = intval($names[1]);
					break;

				case "cellformat":
					$sheet['cellformats'][intval($names[1])] = $names[2];
					$sheet['cellformathash'][$names[2]] = intval($names[1]);
					break;

				case "valueformat":
					$v = self::decodeValue($names[2]);
					$sheet['valueformats'][intval($names[1])] = $v;
					$sheet['valueformathash'][$v] = intval($names[1]);
					break;

				default:
					break;
			}
		}

		return $sheet;
	}


	public static function parseCell($parts, $j)
	{
		$cell = array();
		while ($t = @$parts[$j++]) {
			switch ($t) {
				case "v":
					$cell['datavalue'] = doubleval(self::decodeValue($parts[$j++]));
					$cell['datatype'] = "v";
					$cell['valuetype'] = "n";
					break;
				case "t":
					$cell['datavalue'] = strval(self::decodeValue($parts[$j++]));
					$cell['datatype'] = "t";
					$cell['valuetype'] = "t";
					break;
				case "vt":
					$v = $parts[$j++];
					$cell['valuetype'] = $v;
					$cell['datatype'] = $v[0] == "n" ? "v" : "t";
					$cell['datavalue'] = self::decodeValue($parts[$j++]);
					break;
				case "vtf":
					$cell['valuetype'] = strval($parts[$j++]);
					$cell['datavalue'] = self::decodeValue($parts[$j++]);
					$cell['formula'] = strval(self::decodeValue($parts[$j++]));
					$cell['datatype'] = "f";
					break;
				case "vtc":
					$cell['valuetype'] = strval($parts[$j++]);
					$cell['datavalue'] = self::decodeValue($parts[$j++]);
					$cell['formula'] = strval(self::decodeValue($parts[$j++]));
					$cell['datatype'] = "c";
					break;
				case "e":
					$cell['errors'] = strval(self::decodeValue($parts[$j++]));
					break;
				case "b":
					$cell['bt'] = intval($parts[$j++]);
					$cell['br'] = intval($parts[$j++]);
					$cell['bb'] = intval($parts[$j++]);
					$cell['bl'] = intval($parts[$j++]);
					break;
				case "l":
					$cell['layout'] = intval($parts[$j++]);
					break;
				case "f":
					$cell['font'] = intval($parts[$j++]);
					break;
				case "c":
					$cell['color'] = intval($parts[$j++]);
					break;
				case "bg":
					$cell['bgcolor'] = intval($parts[$j++]);
					break;
				case "cf":
					$cell['cellformat'] = intval($parts[$j++]);
					break;
				case "ntvf":
					$cell['nontextvalueformat'] = intval($parts[$j++]);
					break;
				case "tvf":
					$cell['textvalueformat'] = intval($parts[$j++]);
					break;
				case "colspan":
					$cell['colspan'] = intval($parts[$j++]);
					break;
				case "rowspan":
					$cell['rowspan'] = intval($parts[$j++]);
					break;
				case "cssc":
					$cell['cssc'] = strval($parts[$j++]);
					break;
				case "csss":
					$cell['csss'] = strval(self::decodeValue($parts[$j++]));
					break;
				case "mod":
					$j += 1;
					break;
				case "comment":
					$cell['comment'] = strval(self::decodeValue($parts[$j++]));
					break;
				case "ro":
					$cell['readonly'] = strcmp(strtolower(strval(self::decodeValue($parts[$j++]))), "yes") == 0;
					break;
			}
		}
		return $cell;
	}

	public static function parseCellValue($parts, $j)
	{
		$cell = array();
		while ($t = @$parts[$j++]) {
			switch ($t) {
				case "v":
					$cell['datavalue'] = doubleval(self::decodeValue($parts[$j++]));
					break 2; // first time for me :-)
				case "t":
					$cell['datavalue'] = strval(self::decodeValue($parts[$j++]));
					break 2;
				case "vt":
					$j++;
					$cell['datavalue'] = self::decodeValue($parts[$j++]);
					break 2;
				case "vtf":
					$j++;
					$cell['datavalue'] = self::decodeValue($parts[$j++]);
					break 2;
				case "vtc":
					$j++;
					$cell['datavalue'] = self::decodeValue($parts[$j++]);
					break 2;
			}
		}

		return $cell;
	}

	public static function saveCell($cell)
	{
		$line = 'cell:' . self::crToCoord($cell['pos'][0], $cell['pos'][1]);
		if (isset($cell['datavalue'])) {
			$value = self::encodeValue($cell['datavalue']);
			if ($cell['datatype'] == 'v') {
				if ($cell['valuetype'] == 'n') $line .= ':v:' . $value;
				else $line .= ':vt:' . $cell['valuetype'] . ':' . $value;
			} else if ($cell['datatype'] == 't') {
				if ($cell['valuetype'] == 't') $line .= ':t:' . $value;
				else $line .= ':vt:' . $cell['valuetype'] . ':' . $value;
			} else if (isset($cell['formula'])) {
				$formula = self::encodeValue($cell['formula']);
				if ($cell['datatype'] == 'f')
					$line .= ':vtf:' . $cell['valuetype'] . ':' . $value . ':' . $formula;
				else if ($cell['datatype'] == 'c')
					$line .= ':vtc:' . $cell['valuetype'] . ':' . $value . ':' . $formula;
			}
		}

		if (isset($cell['errors'])) {
			$line .= ':e:' . self::encodeValue($cell['errors']);
		}

		if (!empty($cell['readonly'])) {
			$line .= ':ro:yes';
		}

		$t = @$cell['bt'];
		$r = @$cell['br'];
		$b = @$cell['bb'];
		$l = @$cell['bl'];

		if ($t || $r || $b || $l)
			$line .= ':b:' . $t . ':' . $r . ':' . $b . ':' . $l;

		if (isset($cell['layout'])) $line .= ':l:' . $cell['layout'];
		if (isset($cell['font'])) $line .= ':f:' . $cell['font'];
		if (isset($cell['color'])) $line .= ':c:' . $cell['color'];
		if (isset($cell['bgcolor'])) $line .= ':bg:' . $cell['bgcolor'];
		if (isset($cell['cellformat'])) $line .= ':cf:' . $cell['cellformat'];
		if (isset($cell['textvalueformat'])) $line .= ':tvf:' . $cell['textvalueformat'];
		if (isset($cell['nontextvalueformat'])) $line .= ':ntvf:' . $cell['nontextvalueformat'];
		if (isset($cell['colspan'])) $line .= ':colspan:' . $cell['colspan'];
		if (isset($cell['rowspan'])) $line .= ':rowspan:' . $cell['rowspan'];
		if (isset($cell['cssc'])) $line .= ':cssc:' . $cell['cssc'];
		if (isset($cell['csss'])) $line .= ':csss:' . self::encodeValue($cell['csss']);
		if (isset($cell['mod'])) $line .= ':mod:' . $cell['mod'];
		if (isset($cell['comment'])) $line .= ':comment:' . self::encodeValue($cell['comment']);

		return $line;
	}

	public static function crToCoord($c, $r, $return_as_array = FALSE)
	{
		$letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M",
			"N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		if ($c < 1) $c = 1;
		if ($c > 702) $c = 702; // maximum number of columns - ZZ
		if ($r < 1) $r = 1;
		$collow = ($c - 1) % 26;
		$colhigh = floor(($c - 1) / 26);

		if ($colhigh) {
			$result = array($letters[$colhigh - 1] . $letters[$collow], $r);
		} else {
			$result = array($letters[$collow], $r);
		}

		return $return_as_array ? $result : $result[0] . $result[1];
	}

	public static function encodeValue($s)
	{
		if (!is_string($s)) return $s;
		return str_replace(
			array('\\', ':', "\n"),
			array('\\b', '\\c', '\\n'),
			$s
		);
	}

	public static function decodeValue($s)
	{
		if (!is_string($s)) return $s;

		if (strstr($s, '\\') === FALSE) return $s; // for performace reasons: replace nothing takes up time

		$r = str_replace('\\c', ':', $s);
		$r = str_replace('\\n', "\n", $r);

		return str_replace('\\b', '\\', $r);
	}

	public static function cellFormatParsefont($cell, $sheet)
	{
		print_r($cell, $sheet);
		if (empty($cell['font'])) return FALSE;
		$parts = array();
		preg_match('/^(\*|\S+? \S+?) (\S+?) (\S.*)$/', $sheet['fonts'][$cell['font']], $parts);
		$font = array();
		if ($parts[3] != '*') $font['family'] = $parts[3];
		if ($parts[2] != '*') $font['size'] = $parts[2];
		if ($parts[1] != '*') {
			$font['bold'] = strpos($parts[1], 'bold') !== FALSE;
			$font['italic'] = strpos($parts[1], 'italic') !== FALSE;
		}
		return $font;
	}

	public static function coordToCr($coord)
	{
		static $coordToCr = array();
		if (isset($coordToCr[$coord])) return $coordToCr[$coord];

		$c = 0;
		$r = 0;

		for ($i = 0; $i < strlen($coord); $i++) { // this was faster than using regexes; assumes well-formed
			$ch = ord(substr($coord, $i, 1));

			if ($ch == 36) ; // skip $'s
			else if ($ch <= 57) $r = 10 * $r + $ch - 48;
			else if ($ch >= 97) $c = 26 * $c + $ch - 96;
			else if ($ch >= 65) $c = 26 * $c + $ch - 64;
		}

		$coordToCr[$coord] = array($c, $r);

		return $coordToCr[$coord];
	}

	public static function cellFormatParseColor($cell, $sheet, $color)
	{
		if (empty($cell[$color])) return FALSE;

		$parts = array();

		preg_match('/^rgb\((\d+?),\s*(\d+?),\s*(\d+?)\)\s*$/', $sheet['colors'][$cell[$color]], $parts);
		$rgb = array(
			'r' => intval($parts[1]),
			'g' => intval($parts[2]),
			'b' => intval($parts[3]),
		);

		return $rgb;
	}

	public static function cellFormatParseLayout($cell, $sheet)
	{
		if (empty($cell['layout'])) return FALSE;

		$parts = array();

		preg_match('/^padding:\s*(\S+)\s+(\S+)\s+(\S+)\s+(\S+);vertical-align:\s*(\S+);$/', $sheet['layouts'][$cell['layout']], $parts);

		$layout = array();
		if ($parts[1] != '*') $layout['padtop'] = str_replace('px', '', $parts[1]);
		if ($parts[2] != '*') $layout['padright'] = str_replace('px', '', $parts[2]);
		if ($parts[3] != '*') $layout['padbottom'] = str_replace('px', '', $parts[3]);
		if ($parts[4] != '*') $layout['padleft'] = str_replace('px', '', $parts[4]);
		if ($parts[5] != '*') $layout['alignvert'] = $parts[5];

		return $layout;
	}

	public static function cellFormatParseBorder($cell, $sheet, $attrib)
	{
		if (empty($cell[$attrib])) return FALSE;

		$parts = array();

		preg_match('/^(\S+)\s+(\S+)\s+(\S.+)$/', $sheet['borderstyles'][$cell[$attrib]], $parts);

		$border['thickness'] = $parts[1];
		$border['style'] = $parts[2];

		$color = array();

		preg_match('/^rgb\((\d+?),\s*(\d+?),\s*(\d+?)\)\s*$/', $parts[3], $color);

		$border['color'] = array(
			'r' => intval($color[1]),
			'g' => intval($color[2]),
			'b' => intval($color[3]),
		);

		return $border;
	}

	public static function rgb2html($rgbtext, $includeHash = false)
	{
		preg_match_all('/\((.*?)\)/', $rgbtext, $matches);

		$rgb = explode(",", str_replace(" ", "", $matches[1][0]));
		$r = intval($rgb[0]);
		$g = intval($rgb[1]);
		$b = intval($rgb[2]);

		$hex = '';
		if ($includeHash) {
			$hex = '#';
		}

		if (is_array($r) && sizeof($r) == 3)
			list($r, $g, $b) = $r;

		$r = intval($r);
		$g = intval($g);
		$b = intval($b);

		$r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
		$g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
		$b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

		$color = (strlen($r) < 2 ? '0' : '') . $r;
		$color .= (strlen($g) < 2 ? '0' : '') . $g;
		$color .= (strlen($b) < 2 ? '0' : '') . $b;
		return $hex . $color;
	}


	function socialcalc_default_edit($sheet)
	{
		return array(
			'rowpanes' => array('first' => 1, 'last' => isset($sheet['attribs']['lastrow']) ? $sheet['attribs']['lastrow'] : 1),
			'colpanes' => array('first' => 1, 'last' => isset($sheet['attribs']['lastcol']) ? $sheet['attribs']['lastcol'] : 1),
			'ecell' => array('coord' => 'A1'),
		);
	}

	function socialcalc_default_audit($sheet)
	{
		return '';
	}

	public function socialcalc_save($scData)
	{

		define('SOCIALCALC_MULTIPART_BOUNDARY', 'SocialCalcSpreadsheetControlSave');

		$result = "socialcalc:version:1.0\n" .
			"MIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=" . SOCIALCALC_MULTIPART_BOUNDARY . "\n" .
			"--" . SOCIALCALC_MULTIPART_BOUNDARY . "\nContent-type: text/plain; charset=UTF-8\n\n" .
			"# SocialCalc Spreadsheet Control Save\nversion:1.0\npart:sheet\npart:edit\npart:audit\n" .
			"--" . SOCIALCALC_MULTIPART_BOUNDARY . "\nContent-type: text/plain; charset=UTF-8\n\n" .
			self::socialcalc_save_sheet($scData['sheet']) .
			"--" . SOCIALCALC_MULTIPART_BOUNDARY . "\nContent-type: text/plain; charset=UTF-8\n\n" .
			self::socialcalc_save_edit($scData['edit']) .
			"--" . SOCIALCALC_MULTIPART_BOUNDARY . "\nContent-type: text/plain; charset=UTF-8\n\n" .
			self::socialcalc_save_audit($scData['audit']) .
			"--" . SOCIALCALC_MULTIPART_BOUNDARY . "--\n";

		return $result;
	}

	public function socialcalc_save_edit($edit)
	{
		$result = "version:1.0\n";

		if (!empty($edit['rowpanes'])) foreach ($edit['rowpanes'] as $i => $rowpane) {
			$result .= "rowpane:" . $i . ":" . $rowpane['first'] . ":" . $rowpane['last'] . "\n";
		}
		if (!empty($edit['colpanes'])) foreach ($edit['colpanes'] as $i => $colpane) {
			$result .= "colpane:" . $i . ":" . $colpane['first'] . ":" . $colpane['last'] . "\n";
		}

		if (isset($edit['ecell'])) {
			$result .= "ecell:" . $edit['ecell']['coord'] . "\n";
		}

		if (!empty($edit['range']['hasrange'])) {
			$result .= "range:" . $edit['range']['anchorcoord'] . ":" . $edit['range']['top'] . ":" . $edit['range']['bottom'] . ":" . $edit['range']['left'] . ":" . $edit['range']['right'] . "\n";
		}

		return $result;
	}


	public function socialcalc_save_audit($audit)
	{
		return '';
	}

	public function socialcalc_save_sheet($sheet)
	{


		$sheetar = array();
		$sheetar[] = 'version:1.5';
		if (!empty($sheet['cells'])) foreach ($sheet['cells'] as $cell) {
			$sheetar[] = self::saveCell($cell);
		}

		if (isset($sheet['colattribs'])) {
			if (!empty($sheet['colattribs']['width'])) foreach ($sheet['colattribs']['width'] as $col => $width) {
				$coord = self::crToCoord($col, 1);
				$sheetar[] = 'col:' . substr($coord, 0, -1) . ':w:' . $width;
			}
			if (!empty($sheet['colattribs']['hide'])) foreach ($sheet['colattribs']['hide'] as $col => $hide) {
				$coord = self::crToCoord($col, 1);
				$sheetar[] = 'col:' . substr($coord, 0, -1) . ':hide:' . $hide;
			}
		}


		if (isset($sheet['rowattribs'])) {
			if (!empty($sheet['rowattribs']['height'])) foreach ($sheet['rowattribs']['height'] as $row => $height) {
				$sheetar[] = 'row:' . $row . ':h:' . $height;
			}
			if (!empty($sheet['rowattribs']['hide'])) foreach ($sheet['rowattribs']['hide'] as $row => $hide) {
				$sheetar[] = 'row:' . $row . ':hide:' . $hide;
			}
		}
		if (!empty($sheet['fonts'])) foreach ($sheet['fonts'] as $fid => $font) {
			$sheetar[] = 'font:' . $fid . ':' . $font;
		}
		if (!empty($sheet['borderstyles'])) foreach ($sheet['borderstyles'] as $bsid => $borderstyle) {
			$sheetar[] = 'border:' . $bsid . ':' . $borderstyle;
		}
		if (!empty($sheet['cellformats'])) foreach ($sheet['cellformats'] as $cfid => $cellformat) {
			$sheetar[] = 'cellformat:' . $cfid . ':' . self::encodeValue($cellformat);
		}
		if (!empty($sheet['layouts'])) foreach ($sheet['layouts'] as $lid => $layout) {
			$sheetar[] = 'layout:' . $lid . ':' . $layout;
		}
		if (!empty($sheet['colors'])) foreach ($sheet['colors'] as $cid => $color) {
			$sheetar[] = 'color:' . $cid . ':' . $color;
		}
		if (!empty($sheet['valueformats'])) foreach ($sheet['valueformats'] as $vfid => $valueformat) {
			$sheetar[] = 'valueformat:' . $vfid . ':' . self::encodeValue($valueformat);
		}
		if (!empty($sheet['names'])) foreach ($sheet['names'] as $name => $nameinfo) {
			$sheetar[] = 'name:' . self::encodeValue($name) . ':' . self::encodeValue($nameinfo['desc']) . ':' . self::encodeValue($nameinfo['definition']);
		}

		$sheetfields = array(
			'lastcol' => 'c',
			'lastrow' => 'r',
			'defaultcolwidth' => 'w',
			'defaultrowheight' => 'h',
			'defaulttextformat' => 'tf',
			'defaultnontextformat' => 'ntf',
			'defaulttextvalueformat' => 'tvf',
			'defaultnontextvalueformat' => 'ntvf',
			'defaultlayout' => 'layout',
			'defaultfont' => 'font',
			'defaultcolor' => 'color',
			'defaultbgcolor' => 'bgcolor',
			'circularreferencecell' => 'circularreferencecell',
			'recalc' => 'recalc',
			'needsrecalc' => 'needsrecalc',
			'usermaxcol' => 'usermaxcol',
			'usermaxrow' => 'usermaxrow',
		);
		$sheetattribs = array();
		foreach ($sheetfields as $key => $attrib) {
			if (isset($sheet['attribs'][$key])) {
				$sheetattribs[] = $attrib . ':' . $sheet['attribs'][$key];
			}
		}
		if ($sheetattribs) {
			$sheetar[] = 'sheet:' . implode(':', $sheetattribs);
		}

		return implode("\n", $sheetar) . "\n";
	}
}