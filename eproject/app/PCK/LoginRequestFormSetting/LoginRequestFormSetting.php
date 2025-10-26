<?php namespace PCK\LoginRequestFormSetting;

use Illuminate\Database\Eloquent\Model;

class LoginRequestFormSetting extends Model
{
    protected $fillable = ['instructions', 'include_instructions', 'disclaimer', 'include_disclaimer'];
}