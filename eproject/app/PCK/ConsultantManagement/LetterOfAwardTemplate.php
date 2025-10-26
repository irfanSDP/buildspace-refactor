<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Helpers\StringOperations;

use PCK\ConsultantManagement\LetterOfAwardTemplateClause;

class LetterOfAwardTemplate extends Model
{
    protected $table = 'consultant_management_letter_of_award_templates';

    protected $fillable = ['title', 'letterhead', 'signatory'];

    public function clauses()
    {
        return $this->hasMany(LetterOfAwardTemplateClause::class, 'template_id')->orderBy('sequence_number', 'asc');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getShortTitleAttribute()
    {
        return StringOperations::shorten($this->title, 60);
    }

    public function deletable()
    {
        return true;
    }

    public function getStructuredClauses($withId = true)
    {
        $rootClauses = LetterOfAwardTemplateClause::getRootClauses($this);

        $clausesArray = [];

        foreach($rootClauses as $clause)
        {
            $data = [
                'content'          => $clause->content,
                'displayNumbering' => $clause->display_numbering,
                'sequenceNumber'   => $clause->sequence_number,
                'parentId'         => $clause->parent_id,
                'children'         => $this->getChildrenOfNode($clause->id, $withId),
            ];

            if($withId)
            {
                $data['id'] = $clause->id;
            }

            array_push($clausesArray, $data);
        }

        return $clausesArray;
    }

    private function getChildrenOfNode($parentId, $withId = true)
    {
        $childrenArray = [];

        $children = LetterOfAwardTemplateClause::where('template_id', $this->id)
            ->where('parent_id', $parentId)
            ->orderBy('sequence_number', 'asc')
            ->get();

        if( $children->isEmpty() ) return $childrenArray;

        foreach($children as $child)
        {
            $data = [
                'content'          => $child->content,
                'displayNumbering' => $child->display_numbering,
                'sequenceNumber'   => $child->sequence_number,
                'parentId'         => $child->parent_id,
                'children'         => $this->getChildrenOfNode($child->id, $withId),
            ];

            if($withId)
            {
                $data['id'] = $child->id;
            }

            array_push($childrenArray, $data);
        }

        return $childrenArray;
    }

    public function updateOrCreateClauses(Array $data, $sequenceNumber, LetterOfAwardTemplateClause $parent = null)
    {
        $isExistingClause = array_key_exists('id', $data);
        $hasChildren      = array_key_exists('children', $data);

        if( $isExistingClause )
        {
            $clause = LetterOfAwardTemplateClause::find($data['id']);
            if(!$clause)
            {
                return false;
            }
        }
        else
        {
            $clause              = new LetterOfAwardTemplateClause();
            $clause->template_id = $this->id;
        }

        $clause->content           = isset( $data['content'] ) ? $data['content'] : '';
        $clause->display_numbering = ( $data['displayNumbering'] === 'true' );
        $clause->sequence_number   = $sequenceNumber;

        if($parent)
        {
            $clause->parent_id = $parent->id;
        }

        $clause->save();

        if( $hasChildren )
        {
            $sequenceNumber = 1;

            foreach($data['children'] as $childData)
            {
                $this->updateOrCreateClauses($childData, $sequenceNumber++, $clause);
            }
        }

    }

    public function deleteClauses(Array $data)
    {
        $clause = LetterOfAwardTemplateClause::find($data['id']);

        if(!$clause)
        {
            return false;
        }

        $children = LetterOfAwardTemplateClause::getChildrenOf($clause)->toArray();

        foreach($children as $child)
        {
            $this->deleteClauses($child);
        }

        $clause->delete();
    }
}