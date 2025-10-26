<?php namespace PCK\Users;

use Illuminate\Database\Eloquent\Model;

class LmsUser extends Model {

    protected $fillable = ['user_id','lms_course_id', 'lms_course_name', 'lms_course_score', 'lms_course_completed', 'lms_course_completed_at'];

}