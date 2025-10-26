<?php namespace PCK\DocumentControlObject;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Users\User;

abstract class DocumentControlObject extends Model {

    protected $table = 'document_control_objects';

    protected $appends = [ 'reference', 'messages' ];

    abstract public function getReferenceAttribute();

    abstract public function canPostMessage(User $user);

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function issuer()
    {
        return $this->belongsTo('PCK\Users\User', 'issuer_id');
    }

    public static function getNextReferenceNumber(Project $project, $messageType)
    {
        return static::where('project_id', '=', $project->id)
            ->where('message_type', '=', $messageType)
            ->max('reference_number') + 1;
    }

    public function messages()
    {
        return $this->hasMany($this->message_type, 'document_control_object_id')
            ->orderBy('sequence_number', 'asc');
    }

    /**
     * Returns only the visible ones.
     *
     * @return mixed
     */
    public function getVisibleMessages()
    {
        return $this->messages->reject(function ($message)
        {
            return ( ! $message->isVisible() );
        });
    }

    public function getLastMessage()
    {
        return $this->messages->last();
    }

    public function getFirstMessage()
    {
        return $this->messages->first();
    }

}