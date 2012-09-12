<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');


function FormatFTPName($str) {
	$strSubChar = '_';	
	$str = strtolower($str);
	$t_Search = array('|[éèêë]|i','|[àâä]|i','|[îï]|i','|[ûùü]|i','|[ôö]|i','|[ç]|i','|[^a-zA-Z0-9]|');
	$t_Replace = array('e','a','i','u','o','c', $strSubChar);
	$str = preg_replace($t_Search, $t_Replace, $str);		
	$str = preg_replace('|' . $strSubChar . '+|', $strSubChar, $str);	
	return $str;
}

function FormatWeight($wt, $dec = 2) {
	$i = 0;
	$tWeights = array('o', 'Ko', 'Mo', 'Go', 'To');
	while($wt > 1024) {
		$i++;
		$wt /= 1024;
	}

	return (round($wt * 10^$dec) / 10^$dec)	 . $tWeights[$i];
}

function FormatDate($tstamp) {
	global $tMonths;
	return date('d', $tstamp) . ' ' . $tMonths[date('n', $tstamp)] . ' ' . date('Y', $tstamp);
}

function FormDate($tstamp) {
	global $tMonths;
	return date('d', $tstamp) . '.' . date('n', $tstamp) . '.' . date('Y', $tstamp);
}


/********************************
 * Retro-support of get_called_class()
 * Tested and works in PHP 5.2.4
 * http://www.sol1.com.au/
 ********************************/
if(!function_exists('get_called_class')) {
function get_called_class($bt = false,$l = 1) {
    if (!$bt) $bt = debug_backtrace();
    if (!isset($bt[$l])) throw new Exception("Cannot find called class -> stack level too deep.");
    if (!isset($bt[$l]['type'])) {
        throw new Exception ('type not set');
    }
    else switch ($bt[$l]['type']) {
        case '::':
            $lines = file($bt[$l]['file']);
            $i = 0;
            $callerLine = '';
            do {
                $i++;
                $callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
            } while (stripos($callerLine,$bt[$l]['function']) === false);
            preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
                        $callerLine,
                        $matches);
            if (!isset($matches[1])) {
                // must be an edge case.
                throw new Exception ("Could not find caller class: originating method call is obscured.");
            }
            switch ($matches[1]) {
                case 'self':
                case 'parent':
                    return get_called_class($bt,$l+1);
                default:
                    return $matches[1];
            }
            // won't get here.
        case '->': switch ($bt[$l]['function']) {
                case '__get':
                    // edge case -> get class of calling object
                    if (!is_object($bt[$l]['object'])) throw new Exception ("Edge case fail. __get called on non object.");
                    return get_class($bt[$l]['object']);
                default: return $bt[$l]['class'];
            }

        default: throw new Exception ("Unknown backtrace method type");
    }
}
}


function getModuleList() {
	$tModules = Module::getAll();
	$optsIdx = array();
	
	$optsIdx[''] = '';	
	
	foreach($tModules as $Module) {
		$tFields = $Module -> getFields();	
		foreach($tFields as $Field) {
			$optsIdx[$Module -> getLabel()][$Field -> getId()] = $Field -> getLabel();	
		}
	}	
	
	return $optsIdx;
}

?>