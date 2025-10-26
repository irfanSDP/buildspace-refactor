<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<div id="content" class="content" style="visibility:hidden; width:185mm; background-color: grey"></div>

</body>
</html>

<script src="{{ asset('js/jquery/dist/jquery.min.js') }}"></script>

<script>
    $('.content').append('{{ addslashes($content) }}');

    var heightInPixels = document.getElementById('content').offsetHeight;

    window.location.replace('{{{ $routeGenerate }}}?h='+heightInPixels);

</script>
