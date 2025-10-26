<?php namespace PCK\WorkCategories;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Buildspace\WorkCategory as BSWorkCategory;

class WorkCategory extends Model {

    use SoftDeletingTrait;

    const UNSPECIFIED_RECORD_NAME = 'Unspecified';
    const IDENTIFIER_MAX_CHARS    = 10;

    protected $fillable = [
        'name',
        'identifier'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $workCategory)
        {
            return $workCategory->buildSpace_store();
        });

        static::updating(function(self $workCategory)
        {
            return $workCategory->buildSpace_update();
        });
    }

    /**
     * Saves the resource in the BuildSpace database.
     *
     * @return bool
     */
    private function buildSpace_store()
    {
        $bsResource       = new BSWorkCategory();
        $bsResource->name = $this->name;

        return $bsResource->save();
    }

    /**
     * Updates the resource in the BuildSpace database.
     * This has to be run BEFORE the actual update in eProject,
     * else the original name (the only field we can identify the BuildSpace resource with)
     * will have been lost.
     *
     * @return mixed
     */
    private function buildSpace_update()
    {
        $originalName = WorkCategory::find($this->id)->name;

        $bsResource = BSWorkCategory::where('name', 'ilike', $originalName)->first();

        if( ! $bsResource )
        {
            // Still saves the resource in eProject if it does not exist in BuildSpace.
            return true;
        }

        $bsResource->name = $this->name;

        return $bsResource->save();
    }

    public function contractors()
    {
        return $this->belongsToMany('PCK\Contractors\Contractor');
    }

    public function projects()
    {
        return $this->hasMany('PCK\Projects\Project');
    }

    public function templateTenderDocumentFolders()
    {
        return $this->belongsToMany('PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolder');
    }

    public function getTemplateTenderDocumentFolder()
    {
        return $this->templateTenderDocumentFolders->first();
    }

    public function templateDocumentFiles()
    {
        return $this->hasMany('PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFile');
    }

    /**
     * Mutator for the column "identifier".
     *
     * @param $value
     */
    public function setIdentifierAttribute($value)
    {
        // Only allow alphabetical characters.
        $regex = "/[^A-Za-z]/";

        $this->attributes['identifier'] = preg_replace($regex, '', $value);
    }

    /**
     * Generates a unique identifier.
     *
     * @param $name
     *
     * @return string
     */
    public static function generateIdentifier($name)
    {
        $generatedIdentifier = false;

        $regex = "/[^A-Za-z]/";
        $name  = preg_replace($regex, '', $name);

        // Generate identifier from name (if possible) - First method.
        $identifier = substr($name, 0, 3);

        $identifier = strtoupper($identifier);

        $count = self::where('identifier', '=', $identifier)->count();

        $isUnique = ( $count == 0 );

        if( $isUnique )
        {
            $generatedIdentifier = true;
        }

        // Generate identifier from name (if possible) - Second method.
        if( ! $generatedIdentifier )
        {
            for($i = self::IDENTIFIER_MAX_CHARS; $i > 0; $i--)
            {
                $identifier = substr($name, 0, $i);

                $count = self::where('identifier', '=', $identifier)->count();

                $isUnique = ( $count == 0 );

                if( $isUnique )
                {
                    $generatedIdentifier = true;
                    break;
                }
            }
        }

        // Generate random string as identifier (last resort, as previous method(s) failed).
        if( ! $generatedIdentifier )
        {
            $isUnique = false;
            while( ! $isUnique )
            {
                $identifier = str_random(self::IDENTIFIER_MAX_CHARS);
                $count      = self::where('identifier', '=', $identifier)->count();
                $isUnique   = ( $count == 0 );
            }
        }

        return $identifier;
    }

    public function vendorWorkCategories()
    {
        return $this->belongsToMany('PCK\VendorWorkCategory\VendorWorkCategory', 'vendor_work_category_work_category');
    }

    public static function getRecordsByIds(Array $ids)
    {
        if(count($ids) == 0) return [];

        $query = "SELECT id, name
                    FROM work_categories 
                    WHERE id IN (" . implode(', ', $ids) . ")
                    ORDER BY id ASC;";

        $queryResult = DB::select(DB::raw($query));

        return array_map(function($record) {
			return trim($record);
		}, array_column($queryResult, 'name'));
    }
}