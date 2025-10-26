<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class AutomateCodeController extends \BaseController
{
    public function getData()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        //$tables = array_slice($tables, 0, 2);
        $functions = []; // To store generated function codes

        foreach ($tables as $table) {
            // Retrieve columns for each table
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
        
            // Capitalize each word except for the first one
            $words = explode('_', $table);
            $capitalizeName = strtolower(array_shift($words)) . implode('', array_map('ucfirst', $words));

            // Start building the controller method dynamically
            $functionCode = "
            public function $capitalizeName()
            {
                \$authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
                \$expectedToken  = \$this->expectedToken;
        
                if (\$authToken !== \$expectedToken) {
                    return \Response::json(['message' => 'Not Authorized'], 401);
                }

                \$items = DB::table('$table')->get();
        
                if (\count(\$items) > 0) {
                    return Response::json([
                        'success' => true,
                        'data' => array_map(function (\$item) {
                            return [";
        
                    // Add column mappings dynamically
                    foreach ($columns as $column) {
                        $functionCode .= "\n                              '$column' => \$item->$column,";
                    }
        
            $functionCode .= "
                            ];
                        }, (array)\$items)
                    ], 200);
                } else {
                    return Response::json([
                        'success' => true,
                        'data' => \$items
                    ], 200);
                }
            }";

            $functions[] = $functionCode;
            echo "<pre><code>" . htmlspecialchars($functionCode) . "</code></pre>";
        }
    }

    public function postData()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        //$tables = array_slice($tables, 0, 2);
        $functions = []; // To store generated function codes
    
        foreach ($tables as $table) {
            // Retrieve columns for each table
            $columns = DB::getSchemaBuilder()->getColumnListing($table);

            // Exclude the 'id' column
            $columns = array_diff($columns, ['id']);
    
            // Capitalize each word except for the first one
            $words = explode('_', $table);
            $capitalizeName = strtolower(array_shift($words)) . implode('', array_map('ucfirst', $words));
    
            // Start building the controller method dynamically
            $functionCode = "
                public function $capitalizeName()
                {
                    \$authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
                    \$expectedToken  = \$this->expectedToken;
            
                    if (\$authToken !== \$expectedToken) {
                        return \Response::json(['message' => 'Not Authorized'], 401);
                    }

                    \$msg = 'Data created successfully';
                    \$success = true;
                    \$httpResponseCode = 201;
                    \$records = \Input::all();
                
                    try {
                        // Insert a new record
                        DB::table('$table')->insert([";
            
            // Add column mappings dynamically with aligned spacing
            foreach ($columns as $column) {
                $functionCode .= "\n                        '$column' => \$records['$column'],";
            }
    
            $functionCode .= "
                        ]);
                    } catch (\Exception \$e) {
                        // If there's an error, catch the exception
                        \$msg = \$e->getMessage(); // Provide the exception message
                        \$success = false;
                    }
                
                    // Return the response
                    return Response::json([
                        'success' => true,
                        'data' => \$msg,
                    ], \$httpResponseCode);
                }";
    
            $functions[] = $functionCode;
            echo "<pre><code>" . htmlspecialchars($functionCode) . "</code></pre>";
        }
    }

    public function putData()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $functions = []; // To store generated function codes
    
        foreach ($tables as $table) {
            // Retrieve columns for each table
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            // Exclude the 'id' column (assuming 'id' is the primary key for update)
            $columns = array_diff($columns, ['id']);
    
            // Capitalize each word except for the first one
            $words = explode('_', $table);
            $capitalizeName = strtolower(array_shift($words)) . implode('', array_map('ucfirst', $words));
    
            // Start building the controller method dynamically
            $functionCode = "
                public function $capitalizeName(\$id)
                {
                    \$authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
                    \$expectedToken  = \$this->expectedToken;
            
                    if (\$authToken !== \$expectedToken) {
                        return \Response::json(['message' => 'Not Authorized'], 401);
                    }

                    \$columns = DB::getSchemaBuilder()->getColumnListing('$table');
                    \$msg = 'Data updated successfully';
                    \$success = true;
                    \$httpResponseCode = 200;
                    \$records = \Input::all();
    
                    try {
                        // Check if the record exists
                        \$recordExists = DB::table('$table')->find(\$id);

                        if (!\$recordExists) {
                            throw new \Exception('Record not found');
                        }

                        // Prepare data for update by filtering out invalid columns
                        \$dataToUpdate = array_intersect_key(\$records, array_flip(\$columns));
    
                        // Check if there's any data to update
                        if (empty(\$dataToUpdate)) {
                            throw new \Exception('No valid columns provided for update');
                        }
    
                        // Update the record
                        DB::table('$table')->where('id', \$id)->update(\$dataToUpdate);
                    } catch (\Exception \$e) {
                        // If there's an error, catch the exception
                        \$msg = \$e->getMessage(); // Provide the exception message
                        \$success = false;
                    }
                    
                    // Return the response
                    return Response::json([
                        'success' => true,
                        'data' => \$msg,
                    ], \$httpResponseCode);
                }";
    
            $functions[] = $functionCode;
            echo "<pre><code>" . htmlspecialchars($functionCode) . "</code></pre>";
        }
    }
    
    public function deleteData()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $functions = []; // To store generated function codes
    
        foreach ($tables as $table) {
            // Capitalize each word except for the first one
            $words = explode('_', $table);
            $capitalizeName = strtolower(array_shift($words)) . implode('', array_map('ucfirst', $words));
    
            // Start building the controller method dynamically
            $functionCode = "
                public function $capitalizeName(\$id)
                {
                    \$authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
                    \$expectedToken  = \$this->expectedToken;
            
                    if (\$authToken !== \$expectedToken) {
                        return \Response::json(['message' => 'Not Authorized'], 401);
                    }

                    \$msg = 'Data deleted successfully';
                    \$success = true;
                    \$httpResponseCode = 200;
                    \$records = \Input::all();
    
                    try {
                        // Check if the record exists
                        \$recordExists = DB::table('$table')->find(\$id);

                        if (!\$recordExists) {
                            throw new \Exception('Record not found');
                        }

                        // Delete the record
                        DB::table('$table')->where('id', \$id)->delete();
                    } catch (\Exception \$e) {
                        // If there's an error, catch the exception
                        \$msg = \$e->getMessage(); // Provide the exception message
                        \$success = false;
                    }
                    
                    // Return the response
                    return Response::json([
                        'success' => true,
                        'data' => \$msg,
                    ], \$httpResponseCode);
                }";
    
            $functions[] = $functionCode;
            echo "<pre><code>" . htmlspecialchars($functionCode) . "</code></pre>";
        }
    }

    public function apiRoutePostman()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        // $tables = array_slice($tables, 0, 2);
        $functions = []; // To store generated function codes

        foreach ($tables as $table) {
            $tableKebabCase = $this->toKebabCase($table);
            $functionCode = "{ route: '$tableKebabCase', name: 'api.$tableKebabCase' },";

            $functions[] = $functionCode;
            echo "<pre><code>" . htmlspecialchars($functionCode) . "</code></pre>";
        }
    }

    public function apiRoute()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        // $tables = array_slice($tables, 0, 2);
        $functions = []; // To store generated function codes

        foreach ($tables as $table) {
            // Capitalize each word except for the first one
            $words = explode('_', $table);
            $capitalizeName = strtolower(array_shift($words)) . implode('', array_map('ucfirst', $words));

            $tableKebabCase = $this->toKebabCase($table);

            $functionCode = "
            //$table
            Route::get('$tableKebabCase', array('as' => 'api.$tableKebabCase', 'uses' => 'Api\GetDataApiController@$capitalizeName' ));
            Route::post('$tableKebabCase', array('as' => 'api.$tableKebabCase.create', 'uses' => 'Api\PostDataApiController@$capitalizeName' ));
            Route::put('$tableKebabCase/{Id}', array('as' => 'api.$tableKebabCase.update', 'uses' => 'Api\PutDataApiController@$capitalizeName' ));
            Route::delete('$tableKebabCase/{Id}', array('as' => 'api.$tableKebabCase.delete', 'uses' => 'Api\DeleteDataApiController@$capitalizeName' ));";

            $functions[] = $functionCode;
            echo "<pre><code>" . htmlspecialchars($functionCode) . "</code></pre>";
        }
    }
    
    public function apiBypassRoute()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        // $tables = array_slice($tables, 0, 2);
        $functions = []; // To store generated function codes

        foreach ($tables as $table) {
            $tableKebabCase = $this->toKebabCase($table);
            $functionCode = 
            "'api.$tableKebabCase.create',\n'api.$tableKebabCase.update',\n'api.$tableKebabCase.delete',";

            $functions[] = $functionCode;
            echo "<pre><code>" . htmlspecialchars($functionCode) . "</code></pre>";
        }
    }

    public function generateJsonData()
    {
        // Fetch the table names from the database
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $jsonData = []; // To hold JSON data for each table
    
        foreach ($tables as $table) {
            // Retrieve columns for each table
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            
            // Create a JSON object for this table
            $tableData = $this->getTableData($columns);
            
            // Add the table data to the overall JSON data
            $jsonData[$table] = $tableData;
        }
    
        // Return the structured JSON response
        return Response::json($jsonData);
    }
    
    // Helper function to get data for a given table's columns
    private function getTableData(array $columns)
    {
        $tableData = [];
    
        foreach ($columns as $column) {
            $tableData[$column] = $this->getDefaultValueForColumn($column);
        }
    
        return $tableData;
    }
    
    // Helper function to get a default value for a given column
    private function getDefaultValueForColumn($column)
    {
        if (strpos($column, 'id') !== false) {
            return 1; // Default for IDs
        } elseif (strpos($column, 'date') !== false || strpos($column, 'time') !== false) {
            return Carbon::now()->toDateTimeString(); // Current date/time
        } elseif (strpos($column, 'email') !== false) {
            return 'example@example.com'; // Default email
        } elseif (strpos($column, 'name') !== false) {
            return 'Sample Name'; // Default name
        } else {
            return ''; // Default empty string for other fields
        }
    }

    public function toKebabCase($tableName) {
        return str_replace('_', '-', $tableName);
    }    

}

?>