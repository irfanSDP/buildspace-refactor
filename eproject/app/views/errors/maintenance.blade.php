<!doctype html>
<title>Site Maintenance</title>
<style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px 20px;
            background-color: #f0f0f0;
        }
        .countdown {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            padding: 20px 0;
        }
        .superadmin-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #bd824a;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            transition: background-color 0.3s ease;
            margin-top: 20px; /* Spacing from countdown */
        }
        .superadmin-button:hover {
            background-color: #874f1a;
        }
        .maintenance-image {
            margin-top: 20px;
            margin-bottom: 4px;
            max-width: 100%;
            border-radius: 5px; 
            height: auto;
            width: 500px;
        }
    </style>
<?php
if (!empty($image)) 
{
    if (file_exists(public_path('/upload/maintenance/'.$id.'/'.$image))) {
        $imageAttributes['path'] = asset('/upload/maintenance/'.$id.'/'.$image);
    }		
}
else
{
    if (file_exists(public_path('/upload/maintenance/0/maintenance.png'))) {
        $imageAttributes['path'] = asset('/upload/maintenance/0/maintenance.png');
    }	
}
?>
<article>
    @if(isset($imageAttributes))
        <img src="{{ $imageAttributes['path'] }}" class="maintenance-image">
    @endif
    <h1>We&rsquo;ll be back soon!</h1>
    <p class="countdown">
        @if ($days > 0)
            {{ $days }} {{ $days === 1 ? 'Day' : 'Days' }}:
        @endif
        {{ $hours }} {{ $hours === 1 ? 'hour' : 'hours' }}:
        {{ $minutes }} {{ $minutes === 1 ? 'minute' : 'minutes' }}:
        {{ $seconds }} {{ $seconds === 1 ? 'second' : 'seconds' }}
    </p>
    <p>{{ $message }}</p>
    <a href="{{ route('maintenance.login') }}" class="superadmin-button">Superadmin Access Only</a>
</article>

