<?php
$label = isset($label) ? $label : null;
$value = isset($value) ? $value : 0;
$max = isset($max) ? $max : 0;
$coloured = isset($coloured) ? $coloured : true;

$lessThanClass = isset($lessThanClass) ? $lessThanClass : 'color-bootstrap-warning';
$equalToClass = isset($equalToClass) ? $equalToClass : 'color-bootstrap-success';
$greaterThanClass = isset($greaterThanClass) ? $greaterThanClass : 'color-bootstrap-warning';

    $class = '';
    if($coloured)
    {
        if($value < $max) $class = $lessThanClass;
        if($value == $max) $class = $equalToClass;
        if($value > $max) $class = $greaterThanClass;
    }
?>

&nbsp;
@if(!is_null($label))
    {{{ $label }}} :
@endif
<span class="{{{ $class }}}">{{{ $value }}}</span> / <strong>{{{ $max }}}</strong>
&nbsp;