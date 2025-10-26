<?php
namespace Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PutDataApiController extends \BaseController
{
    protected $expectedToken = 'omkoFF3J2J6XywgbZF81Si5AK7uJNza6yos0FnrL5RdnTkLacsKS60LxcFxe6mPR';
    
    public function accountCodeSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('account_code_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('account_code_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('account_code_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function additionalElementValues($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('additional_element_values');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('additional_element_values')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('additional_element_values')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function additionalExpenses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('additional_expenses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('additional_expenses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('additional_expenses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function additionalExpenseInterimClaims($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('additional_expense_interim_claims');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('additional_expense_interim_claims')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('additional_expense_interim_claims')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeContractorConfirmDelays($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('ae_contractor_confirm_delays');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('ae_contractor_confirm_delays')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('ae_contractor_confirm_delays')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeFirstLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('ae_first_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('ae_first_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('ae_first_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeFourthLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('ae_fourth_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('ae_fourth_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('ae_fourth_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function accountingReportExportLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('accounting_report_export_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('accounting_report_export_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('accounting_report_export_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function accountingReportExportLogItemCodes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('accounting_report_export_log_item_codes');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('accounting_report_export_log_item_codes')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('accounting_report_export_log_item_codes')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function acknowledgementLetters($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('acknowledgement_letters');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('acknowledgement_letters')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('acknowledgement_letters')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function additionalExpenseClaims($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('additional_expense_claims');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('additional_expense_claims')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('additional_expense_claims')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeThirdLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('ae_third_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('ae_third_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('ae_third_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aiThirdLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('ai_third_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('ai_third_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('ai_third_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function architectInstructionEngineerInstruction($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('architect_instruction_engineer_instruction');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('architect_instruction_engineer_instruction')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('architect_instruction_engineer_instruction')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function apportionmentTypes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('apportionment_types');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('apportionment_types')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('apportionment_types')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function architectInstructionInterimClaims($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('architect_instruction_interim_claims');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('architect_instruction_interim_claims')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('architect_instruction_interim_claims')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function architectInstructionMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('architect_instruction_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('architect_instruction_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('architect_instruction_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function attachedClauseItems($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('attached_clause_items');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('attached_clause_items')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('attached_clause_items')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function calendarSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('calendar_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('calendar_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('calendar_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificateEmailLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('claim_certificate_email_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('claim_certificate_email_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('claim_certificate_email_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function cidbGrades($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('cidb_grades');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('cidb_grades')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('cidb_grades')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function assignCompaniesLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('assign_companies_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('assign_companies_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('assign_companies_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function assignCompanyInDetailLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('assign_company_in_detail_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('assign_company_in_detail_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('assign_company_in_detail_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function authenticationLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('authentication_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('authentication_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('authentication_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function calendars($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('calendars');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('calendars')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('calendars')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificateInvoiceInformationUpdateLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('claim_certificate_invoice_information_update_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('claim_certificate_invoice_information_update_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('claim_certificate_invoice_information_update_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function buildingInformationModellingLevels($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('building_information_modelling_levels');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('building_information_modelling_levels')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('building_information_modelling_levels')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function businessEntityTypes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('business_entity_types');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('business_entity_types')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('business_entity_types')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function cidbCodes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('cidb_codes');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('cidb_codes')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('cidb_codes')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyCidbCode($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_cidb_code');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_cidb_code')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_cidb_code')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificatePaymentNotificationLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('claim_certificate_payment_notification_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('claim_certificate_payment_notification_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('claim_certificate_payment_notification_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyDetailAttachmentSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_detail_attachment_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_detail_attachment_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_detail_attachment_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificatePayments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('claim_certificate_payments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('claim_certificate_payments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('claim_certificate_payments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificatePrintLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('claim_certificate_print_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('claim_certificate_print_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('claim_certificate_print_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function clauses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('clauses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('clauses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('clauses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyImportedUsers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_imported_users');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_imported_users')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_imported_users')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyImportedUsersLog($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_imported_users_log');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_imported_users_log')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_imported_users_log')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyPersonnelSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_personnel_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_personnel_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_personnel_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTenderCallingTenderInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_tender_calling_tender_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_tender_calling_tender_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_tender_calling_tender_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTenderLotInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_tender_lot_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_tender_lot_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_tender_lot_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTenderRotInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_tender_rot_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_tender_rot_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_tender_rot_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTenderTenderAlternatives($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_tender_tender_alternatives');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_tender_tender_alternatives')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_tender_tender_alternatives')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyProject($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_project');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_project')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_project')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyPropertyDevelopers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_property_developers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_property_developers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_property_developers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTemporaryDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_temporary_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_temporary_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_temporary_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTender($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_tender');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_tender')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_tender')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyVendorCategory($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_vendor_category');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_vendor_category')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_vendor_category')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionE($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_approval_document_section_e');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_approval_document_section_e')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_approval_document_section_e')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantQuestionnaires($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_questionnaires');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_questionnaires')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_questionnaires')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCallingRfp($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_calling_rfp');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_calling_rfp')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_calling_rfp')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCompanyRoleLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_company_role_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_company_role_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_company_role_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocuments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_approval_documents');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_approval_documents')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_approval_documents')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionAppendix($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_approval_document_section_appendix');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_approval_document_section_appendix')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_approval_document_section_appendix')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionC($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_approval_document_section_c');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_approval_document_section_c')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_approval_document_section_c')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionD($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_approval_document_section_d');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_approval_document_section_d')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_approval_document_section_d')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionB($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_approval_document_section_b');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_approval_document_section_b')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_approval_document_section_b')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentVerifiers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_approval_document_verifiers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_approval_document_verifiers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_approval_document_verifiers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentVerifierVersions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_approval_document_verifier_versions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_approval_document_verifier_versions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_approval_document_verifier_versions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantQuestionnaireReplies($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_questionnaire_replies');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_questionnaire_replies')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_questionnaire_replies')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCallingRfpVerifiers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_calling_rfp_verifiers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_calling_rfp_verifiers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_calling_rfp_verifiers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCallingRfpCompanies($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_calling_rfp_companies');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_calling_rfp_companies')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_calling_rfp_companies')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCallRfpVerifierVersions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_call_rfp_verifier_versions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_call_rfp_verifier_versions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_call_rfp_verifier_versions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpQuestionnaireReplies($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_rfp_questionnaire_replies');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_rfp_questionnaire_replies')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_rfp_questionnaire_replies')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfp($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_rfp');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_rfp')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_rfp')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpReplyAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_rfp_reply_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_rfp_reply_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_rfp_reply_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpCommonInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_rfp_common_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_rfp_common_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_rfp_common_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardClauses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_letter_of_award_clauses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_letter_of_award_clauses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_letter_of_award_clauses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardTemplateClauses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_letter_of_award_template_clauses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_letter_of_award_template_clauses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_letter_of_award_template_clauses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementContracts($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_contracts');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_contracts')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_contracts')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpProposedFees($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_rfp_proposed_fees');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_rfp_proposed_fees')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_rfp_proposed_fees')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantUsers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_users');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_users')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_users')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementExcludeAttachmentSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_exclude_attachment_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_exclude_attachment_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_exclude_attachment_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantReplyAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_reply_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_reply_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_reply_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_consultant_rfp_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_consultant_rfp_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_consultant_rfp_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementExcludeQuestionnaires($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_exclude_questionnaires');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_exclude_questionnaires')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_exclude_questionnaires')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_letter_of_award_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_letter_of_award_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_letter_of_award_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementListOfConsultants($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_list_of_consultants');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_list_of_consultants')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_list_of_consultants')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardTemplates($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_letter_of_award_templates');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_letter_of_award_templates')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_letter_of_award_templates')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementListOfConsultantVerifiers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_list_of_consultant_verifiers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_list_of_consultant_verifiers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_list_of_consultant_verifiers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementProductTypes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_product_types');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_product_types')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_product_types')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLoaSubsidiaryRunningNumbers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_loa_subsidiary_running_numbers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_loa_subsidiary_running_numbers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_loa_subsidiary_running_numbers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementQuestionnaires($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_questionnaires');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_questionnaires')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_questionnaires')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwards($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_letter_of_awards');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_letter_of_awards')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_letter_of_awards')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementOpenRfpVerifiers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_open_rfp_verifiers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_open_rfp_verifiers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_open_rfp_verifiers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementOpenRfpVerifierVersions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_open_rfp_verifier_versions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_open_rfp_verifier_versions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_open_rfp_verifier_versions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementQuestionnaireOptions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_questionnaire_options');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_questionnaire_options')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_questionnaire_options')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardVerifiers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_letter_of_award_verifiers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_letter_of_award_verifiers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_letter_of_award_verifiers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardVerifierVersions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_letter_of_award_verifier_versions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_letter_of_award_verifier_versions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_letter_of_award_verifier_versions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementListOfConsultantCompanies($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_list_of_consultant_companies');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_list_of_consultant_companies')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_list_of_consultant_companies')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLocVerifierVersions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_loc_verifier_versions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_loc_verifier_versions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_loc_verifier_versions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRecommendationOfConsultantCompanies($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_recommendation_of_consultant_companies');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_recommendation_of_consultant_companies')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_recommendation_of_consultant_companies')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRecommendationOfConsultantVerifiers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_recommendation_of_consultant_verifiers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_recommendation_of_consultant_verifiers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_recommendation_of_consultant_verifiers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpResubmissionVerifiers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_resubmission_verifiers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_resubmission_verifiers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_resubmission_verifiers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpInterviewTokens($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_interview_tokens');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_interview_tokens')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_interview_tokens')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpInterviewConsultants($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_interview_consultants');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_interview_consultants')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_interview_consultants')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpQuestionnaires($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_questionnaires');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_questionnaires')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_questionnaires')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpInterviews($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_interviews');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_interviews')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_interviews')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpQuestionnaireOptions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_questionnaire_options');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_questionnaire_options')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_questionnaire_options')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRolesContractGroupCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_roles_contract_group_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_roles_contract_group_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_roles_contract_group_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpRevisions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_revisions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_revisions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_revisions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpAttachmentSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_attachment_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_attachment_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_attachment_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpDocuments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_documents');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_documents')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_documents')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpResubmissionVerifierVersions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_rfp_resubmission_verifier_versions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_rfp_resubmission_verifier_versions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_rfp_resubmission_verifier_versions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRecommendationOfConsultants($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_recommendation_of_consultants');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_recommendation_of_consultants')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_recommendation_of_consultants')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRocVerifierVersions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_roc_verifier_versions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_roc_verifier_versions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_roc_verifier_versions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSectionDDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_section_d_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_section_d_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_section_d_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSectionDServiceFees($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_section_d_service_fees');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_section_d_service_fees')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_section_d_service_fees')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_group_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_group_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_group_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementVendorCategoriesRfp($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_vendor_categories_rfp');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_vendor_categories_rfp')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_vendor_categories_rfp')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupCategoryPrivileges($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_group_category_privileges');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_group_category_privileges')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_group_category_privileges')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroups($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_groups');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_groups')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_groups')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSubsidiaries($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_subsidiaries');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_subsidiaries')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_subsidiaries')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSectionAppendixDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_section_appendix_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_section_appendix_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_section_appendix_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSectionCDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_section_c_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_section_c_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_section_c_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementUserRoles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_user_roles');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_user_roles')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_user_roles')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementVendorCategoriesRfpAccountCode($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_vendor_categories_rfp_account_code');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_vendor_categories_rfp_account_code')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_vendor_categories_rfp_account_code')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupContractGroupCategory($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_group_contract_group_category');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_group_contract_group_category')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_group_contract_group_category')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupConversation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_group_conversation');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_group_conversation')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_group_conversation')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupDocumentManagementFolder($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_group_document_management_folder');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_group_document_management_folder')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_group_document_management_folder')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupProjectUsers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_group_project_users');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_group_project_users')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_group_project_users')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupTenderDocumentPermissionLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_group_tender_document_permission_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_group_tender_document_permission_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_group_tender_document_permission_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractManagementUserPermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_management_user_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_management_user_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_management_user_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractLimits($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contract_limits');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contract_limits')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contract_limits')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaireReplies($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractor_questionnaire_replies');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractor_questionnaire_replies')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractor_questionnaire_replies')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaireReplyAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractor_questionnaire_reply_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractor_questionnaire_reply_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractor_questionnaire_reply_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorWorkSubcategory($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractor_work_subcategory');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractor_work_subcategory')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractor_work_subcategory')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorRegistrationStatuses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractor_registration_statuses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractor_registration_statuses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractor_registration_statuses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaires($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractor_questionnaires');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractor_questionnaires')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractor_questionnaires')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractors($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractors');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractors')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractors')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function costData($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('cost_data');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('cost_data')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('cost_data')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectStatuses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_statuses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_statuses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_statuses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contracts($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contracts');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contracts')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contracts')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function conversations($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('conversations');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('conversations')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('conversations')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorsCommitmentStatusLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractors_commitment_status_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractors_commitment_status_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractors_commitment_status_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function conversationReplyMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('conversation_reply_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('conversation_reply_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('conversation_reply_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaireQuestions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractor_questionnaire_questions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractor_questionnaire_questions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractor_questionnaire_questions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaireOptions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractor_questionnaire_options');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractor_questionnaire_options')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractor_questionnaire_options')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dailyReport($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('daily_report');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('daily_report')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('daily_report')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dashboardGroups($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('dashboard_groups');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('dashboard_groups')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('dashboard_groups')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function directedTo($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('directed_to');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('directed_to')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('directed_to')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dynamicForms($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('dynamic_forms');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('dynamic_forms')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('dynamic_forms')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingEmailReminders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('e_bidding_email_reminders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('e_bidding_email_reminders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('e_bidding_email_reminders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function documentManagementFolders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('document_management_folders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('document_management_folders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('document_management_folders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function currentCpeGrades($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('current_cpe_grades');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('current_cpe_grades')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('current_cpe_grades')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function currencySettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('currency_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('currency_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('currency_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dailyLabourReports($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('daily_labour_reports');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('daily_labour_reports')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('daily_labour_reports')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dashboardGroupsExcludedProjects($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('dashboard_groups_excluded_projects');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('dashboard_groups_excluded_projects')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('dashboard_groups_excluded_projects')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dashboardGroupsUsers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('dashboard_groups_users');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('dashboard_groups_users')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('dashboard_groups_users')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function defectCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('defect_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('defect_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('defect_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function defectCategoryPreDefinedLocationCode($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('defect_category_pre_defined_location_code');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('defect_category_pre_defined_location_code')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('defect_category_pre_defined_location_code')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function defects($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('defects');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('defects')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('defects')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function developmentTypesProductTypes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('development_types_product_types');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('development_types_product_types')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('development_types_product_types')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function documentControlObjects($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('document_control_objects');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('document_control_objects')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('document_control_objects')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingCommittees($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('e_bidding_committees');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('e_bidding_committees')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('e_bidding_committees')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elementAttributes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('element_attributes');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('element_attributes')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('element_attributes')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailAnnouncementRecipients($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('email_announcement_recipients');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('email_announcement_recipients')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('email_announcement_recipients')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function engineerInstructions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('engineer_instructions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('engineer_instructions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('engineer_instructions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elements($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('elements');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('elements')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('elements')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailNotifications($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('email_notifications');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('email_notifications')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('email_notifications')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailNotificationSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('email_notification_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('email_notification_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('email_notification_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elementDefinitions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('element_definitions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('element_definitions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('element_definitions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailReminderSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('email_reminder_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('email_reminder_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('email_reminder_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('email_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('email_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('email_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elementValues($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('element_values');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('element_values')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('element_values')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elementRejections($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('element_rejections');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('element_rejections')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('element_rejections')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailAnnouncements($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('email_announcements');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('email_announcements')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('email_announcements')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('e_biddings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('e_biddings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('e_biddings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function extensionOfTimes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('extension_of_times');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('extension_of_times')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('extension_of_times')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotFourthLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('eot_fourth_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('eot_fourth_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('eot_fourth_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotSecondLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('eot_second_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('eot_second_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('eot_second_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotThirdLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('eot_third_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('eot_third_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('eot_third_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationAttributes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('external_application_attributes');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('external_application_attributes')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('external_application_attributes')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function expressionOfInterestTokens($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('expression_of_interest_tokens');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('expression_of_interest_tokens')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('expression_of_interest_tokens')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotFirstLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('eot_first_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('eot_first_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('eot_first_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalAppAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('external_app_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('external_app_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('external_app_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalAppCompanyAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('external_app_company_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('external_app_company_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('external_app_company_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotContractorConfirmDelays($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('eot_contractor_confirm_delays');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('eot_contractor_confirm_delays')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('eot_contractor_confirm_delays')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function extensionOfTimeClaims($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('extension_of_time_claims');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('extension_of_time_claims')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('extension_of_time_claims')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationClientOutboundLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('external_application_client_outbound_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('external_application_client_outbound_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('external_application_client_outbound_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationIdentifiers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('external_application_identifiers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('external_application_identifiers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('external_application_identifiers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationClientOutboundAuthorizations($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('external_application_client_outbound_authorizations');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('external_application_client_outbound_authorizations')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('external_application_client_outbound_authorizations')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function fileNodePermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('file_node_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('file_node_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('file_node_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function failedJobs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('failed_jobs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('failed_jobs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('failed_jobs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function financeUserSubsidiaries($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('finance_user_subsidiaries');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('finance_user_subsidiaries')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('finance_user_subsidiaries')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderClauses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_of_tender_clauses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_of_tender_clauses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_of_tender_clauses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationClients($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('external_application_clients');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('external_application_clients')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('external_application_clients')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function fileNodes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('file_nodes');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('file_nodes')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('file_nodes')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formColumns($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_columns');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_columns')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_columns')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formElementMappings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_element_mappings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_element_mappings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_element_mappings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formObjectMappings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_object_mappings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_object_mappings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_object_mappings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_of_tenders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_of_tenders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_of_tenders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderAddresses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_of_tender_addresses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_of_tender_addresses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_of_tender_addresses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_of_tender_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_of_tender_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_of_tender_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderTenderAlternatives($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_of_tender_tender_alternatives');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_of_tender_tender_alternatives')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_of_tender_tender_alternatives')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumPosts($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('forum_posts');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('forum_posts')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('forum_posts')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumPostsReadLog($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('forum_posts_read_log');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('forum_posts_read_log')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('forum_posts_read_log')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function generalSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('general_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('general_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('general_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractContractualClaimResponses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('indonesia_civil_contract_contractual_claim_responses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('indonesia_civil_contract_contractual_claim_responses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('indonesia_civil_contract_contractual_claim_responses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderHeaders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_of_tender_headers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_of_tender_headers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_of_tender_headers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderPrintSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_of_tender_print_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_of_tender_print_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_of_tender_print_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumThreads($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('forum_threads');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('forum_threads')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('forum_threads')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumThreadPrivacyLog($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('forum_thread_privacy_log');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('forum_thread_privacy_log')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('forum_thread_privacy_log')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumThreadUser($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('forum_thread_user');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('forum_thread_user')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('forum_thread_user')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function icInfoGrossValuesAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('ic_info_gross_values_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('ic_info_gross_values_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('ic_info_gross_values_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function icInfoNettAdditionOmissionAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('ic_info_nett_addition_omission_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('ic_info_nett_addition_omission_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('ic_info_nett_addition_omission_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractArchitectInstructions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('indonesia_civil_contract_architect_instructions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('indonesia_civil_contract_architect_instructions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('indonesia_civil_contract_architect_instructions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractAiRfi($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('indonesia_civil_contract_ai_rfi');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('indonesia_civil_contract_ai_rfi')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('indonesia_civil_contract_ai_rfi')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingRankings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('e_bidding_rankings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('e_bidding_rankings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('e_bidding_rankings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractEwLe($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('indonesia_civil_contract_ew_le');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('indonesia_civil_contract_ew_le')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('indonesia_civil_contract_ew_le')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('indonesia_civil_contract_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('indonesia_civil_contract_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('indonesia_civil_contract_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionResults($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_results');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_results')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_results')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionSubmitters($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_submitters');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_submitters')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_submitters')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionLists($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_lists');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_lists')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_lists')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractExtensionsOfTime($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('indonesia_civil_contract_extensions_of_time');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('indonesia_civil_contract_extensions_of_time')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('indonesia_civil_contract_extensions_of_time')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractLossAndExpenses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('indonesia_civil_contract_loss_and_expenses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('indonesia_civil_contract_loss_and_expenses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('indonesia_civil_contract_loss_and_expenses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionGroups($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_groups');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_groups')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_groups')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionGroupInspectionListCategory($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_group_inspection_list_category');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_group_inspection_list_category')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_group_inspection_list_category')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionListCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_list_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_list_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_list_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionGroupUsers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_group_users');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_group_users')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_group_users')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionRoles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_roles');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_roles')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_roles')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionListItems($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_list_items');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_list_items')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_list_items')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionItemResults($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_item_results');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_item_results')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_item_results')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionListCategoryAdditionalFields($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_list_category_additional_fields');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_list_category_additional_fields')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_list_category_additional_fields')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionVerifierTemplate($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspection_verifier_template');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspection_verifier_template')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspection_verifier_template')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardClauseComments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('letter_of_award_clause_comments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('letter_of_award_clause_comments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('letter_of_award_clause_comments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspections($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('inspections');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('inspections')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('inspections')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function interimClaimInformations($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('interim_claim_informations');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('interim_claim_informations')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('interim_claim_informations')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardClauses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('letter_of_award_clauses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('letter_of_award_clauses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('letter_of_award_clauses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardContractDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('letter_of_award_contract_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('letter_of_award_contract_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('letter_of_award_contract_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('letter_of_award_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('letter_of_award_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('letter_of_award_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function labours($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('labours');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('labours')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('labours')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function instructionsToContractors($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('instructions_to_contractors');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('instructions_to_contractors')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('instructions_to_contractors')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardPrintSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('letter_of_award_print_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('letter_of_award_print_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('letter_of_award_print_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardSignatories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('letter_of_award_signatories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('letter_of_award_signatories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('letter_of_award_signatories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function languages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('languages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('languages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('languages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function licenses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('licenses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('licenses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('licenses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function lossOrAndExpenses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('loss_or_and_expenses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('loss_or_and_expenses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('loss_or_and_expenses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeFourthLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('loe_fourth_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('loe_fourth_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('loe_fourth_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeSecondLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('loe_second_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('loe_second_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('loe_second_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeThirdLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('loe_third_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('loe_third_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('loe_third_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function lossOrAndExpenseClaims($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('loss_or_and_expense_claims');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('loss_or_and_expense_claims')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('loss_or_and_expense_claims')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loginRequestFormSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('login_request_form_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('login_request_form_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('login_request_form_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function lossOrAndExpenseInterimClaims($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('loss_or_and_expense_interim_claims');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('loss_or_and_expense_interim_claims')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('loss_or_and_expense_interim_claims')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeFirstLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('loe_first_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('loe_first_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('loe_first_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwards($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('letter_of_awards');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('letter_of_awards')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('letter_of_awards')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardUserPermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('letter_of_award_user_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('letter_of_award_user_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('letter_of_award_user_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeContractorConfirmDelays($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('loe_contractor_confirm_delays');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('loe_contractor_confirm_delays')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('loe_contractor_confirm_delays')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function machinery($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('machinery');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('machinery')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('machinery')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingBids($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('e_bidding_bids');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('e_bidding_bids')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('e_bidding_bids')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function migrations($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('migrations');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('migrations')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('migrations')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function myCompanyProfiles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('my_company_profiles');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('my_company_profiles')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('my_company_profiles')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function notificationGroups($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('notification_groups');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('notification_groups')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('notification_groups')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectForumThreads($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('object_forum_threads');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('object_forum_threads')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('object_forum_threads')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectFields($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('object_fields');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('object_fields')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('object_fields')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncCompanies($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_companies');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_companies')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_companies')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncDefectCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_defect_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_defect_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_defect_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncDefectCategoryTrades($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_defect_category_trades');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_defect_category_trades')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_defect_category_trades')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncDefects($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_defects');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_defects')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_defects')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjectLabourRateContractors($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_project_labour_rate_contractors');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_project_labour_rate_contractors')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_project_labour_rate_contractors')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjectLabourRateTrades($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_project_labour_rate_trades');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_project_labour_rate_trades')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_project_labour_rate_trades')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjectLabourRates($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_project_labour_rates');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_project_labour_rates')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_project_labour_rates')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjectStructureLocationCodes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_project_structure_location_codes');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_project_structure_location_codes')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_project_structure_location_codes')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjects($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_projects');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_projects')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_projects')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncSiteManagementDefects($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_site_management_defects');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_site_management_defects')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_site_management_defects')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncTrades($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_trades');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_trades')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_trades')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncUploads($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('mobile_sync_uploads');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('mobile_sync_uploads')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('mobile_sync_uploads')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function modulePermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('module_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('module_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('module_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function moduleUploadedFiles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('module_uploaded_files');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('module_uploaded_files')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('module_uploaded_files')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function notificationCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('notification_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('notification_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('notification_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function notifications($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('notifications');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('notifications')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('notifications')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('object_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('object_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('object_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderBanners($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_banners');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_banners')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_banners')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationTenderAnalysisTableEditLog($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_award_recommendation_tender_analysis_table_edit_log');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_award_recommendation_tender_analysis_table_edit_log')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_award_recommendation_tender_analysis_table_edit_log')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingEmailReminderRecipients($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('e_bidding_email_reminder_recipients');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('e_bidding_email_reminder_recipients')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('e_bidding_email_reminder_recipients')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationTenderSummary($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_award_recommendation_tender_summary');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_award_recommendation_tender_summary')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_award_recommendation_tender_summary')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderPageInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_page_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_page_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_page_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderPersonInCharges($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_person_in_charges');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_person_in_charges')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_person_in_charges')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderTenderDocuments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_tender_documents');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_tender_documents')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_tender_documents')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationBillDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_award_recommendation_bill_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_award_recommendation_bill_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_award_recommendation_bill_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectTags($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('object_tags');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('object_tags')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('object_tags')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAnnouncements($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_announcements');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_announcements')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_announcements')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderIndustryCodes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_industry_codes');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_industry_codes')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_industry_codes')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderNews($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_news');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_news')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_news')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationFiles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_award_recommendation_files');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_award_recommendation_files')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_award_recommendation_files')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_award_recommendation');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_award_recommendation')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_award_recommendation')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderVerifierLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_verifier_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_verifier_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_verifier_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderItemProjectTenders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('order_item_project_tenders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('order_item_project_tenders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('order_item_project_tenders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderItemVendorRegPayments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('order_item_vendor_reg_payments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('order_item_vendor_reg_payments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('order_item_vendor_reg_payments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderItems($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('order_items');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('order_items')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('order_items')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderPayments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('order_payments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('order_payments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('order_payments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderSubs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('order_subs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('order_subs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('order_subs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('orders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('orders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('orders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function passwordReminders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('password_reminders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('password_reminders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('password_reminders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function paymentGatewayResults($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('payment_gateway_results');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('payment_gateway_results')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('payment_gateway_results')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function paymentGatewaySettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('payment_gateway_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('payment_gateway_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('payment_gateway_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function processorDeleteCompanyLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('processor_delete_company_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('processor_delete_company_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('processor_delete_company_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function procurementMethods($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('procurement_methods');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('procurement_methods')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('procurement_methods')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderTenderRequirements($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_tender_requirements');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_tender_requirements')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_tender_requirements')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function pam2006ProjectDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('pam_2006_project_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('pam_2006_project_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('pam_2006_project_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function paymentSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('payment_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('payment_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('payment_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectModulePermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_module_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_module_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_module_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportChartPlots($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_chart_plots');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_chart_plots')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_chart_plots')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportCharts($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_charts');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_charts')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_charts')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportTypeMappings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_type_mappings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_type_mappings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_type_mappings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportNotificationContents($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_notification_contents');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_notification_contents')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_notification_contents')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportNotificationPeriods($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_notification_periods');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_notification_periods')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_notification_periods')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportNotificationRecipients($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_notification_recipients');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_notification_recipients')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_notification_recipients')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportNotifications($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_notifications');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_notifications')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_notifications')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportColumns($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_columns');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_columns')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_columns')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectLabourRates($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_labour_rates');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_labour_rates')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_labour_rates')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectContractGroupTenderDocumentPermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_contract_group_tender_document_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_contract_group_tender_document_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_contract_group_tender_document_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectContractManagementModules($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_contract_management_modules');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_contract_management_modules')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_contract_management_modules')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectDocumentFiles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_document_files');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_document_files')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_document_files')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportActionLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_action_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_action_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_action_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReports($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_reports');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_reports')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_reports')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportTypes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_types');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_types')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_types')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportUserPermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_report_user_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_report_user_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_report_user_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectRoles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_roles');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_roles')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_roles')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForInformationMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_information_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_information_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_information_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectTrackRecordSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_track_record_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_track_record_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_track_record_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function propertyDevelopers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('property_developers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('property_developers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('property_developers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function purgedVendors($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('purged_vendors');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('purged_vendors')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('purged_vendors')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForInspectionReplies($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_inspection_replies');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_inspection_replies')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_inspection_replies')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationCategoryKpiLimitUpdateLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_variation_category_kpi_limit_update_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_variation_category_kpi_limit_update_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_variation_category_kpi_limit_update_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationContractAndContingencySum($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_variation_contract_and_contingency_sum');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_variation_contract_and_contingency_sum')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_variation_contract_and_contingency_sum')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_variation_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_variation_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_variation_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForInspections($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_inspections');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_inspections')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_inspections')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectSectionalCompletionDates($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('project_sectional_completion_dates');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('project_sectional_completion_dates')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('project_sectional_completion_dates')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForInspectionInspections($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_inspection_inspections');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_inspection_inspections')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_inspection_inspections')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationActionLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_variation_action_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_variation_action_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_variation_action_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationFiles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_variation_files');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_variation_files')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_variation_files')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function rejectedMaterials($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('rejected_materials');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('rejected_materials')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('rejected_materials')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationUserPermissionGroups($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_variation_user_permission_groups');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_variation_user_permission_groups')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_variation_user_permission_groups')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariations($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_variations');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_variations')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_variations')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function scheduledMaintenance($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('scheduled_maintenance');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('scheduled_maintenance')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('scheduled_maintenance')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function sentTenderRemindersLog($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('sent_tender_reminders_log');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('sent_tender_reminders_log')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('sent_tender_reminders_log')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementMcar($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_mcar');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_mcar')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_mcar')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestsForInspection($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('requests_for_inspection');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('requests_for_inspection')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('requests_for_inspection')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementMcarFormResponses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_mcar_form_responses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_mcar_form_responses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_mcar_form_responses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryGeneralFormResponses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_site_diary_general_form_responses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_site_diary_general_form_responses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_site_diary_general_form_responses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryLabours($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_site_diary_labours');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_site_diary_labours')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_site_diary_labours')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationUserPermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('request_for_variation_user_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('request_for_variation_user_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('request_for_variation_user_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function riskRegisterMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('risk_register_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('risk_register_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('risk_register_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementDefectBackchargeDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_defect_backcharge_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_defect_backcharge_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_defect_backcharge_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementDefectFormResponses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_defect_form_responses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_defect_form_responses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_defect_form_responses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryWeathers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_site_diary_weathers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_site_diary_weathers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_site_diary_weathers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementUserPermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_user_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_user_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_user_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function subsidiaries($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('subsidiaries');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('subsidiaries')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('subsidiaries')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function subsidiaryApportionmentRecords($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('subsidiary_apportionment_records');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('subsidiary_apportionment_records')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('subsidiary_apportionment_records')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function supplierCreditFacilities($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('supplier_credit_facilities');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('supplier_credit_facilities')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('supplier_credit_facilities')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryMachinery($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_site_diary_machinery');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_site_diary_machinery')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_site_diary_machinery')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function systemModuleElements($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('system_module_elements');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('system_module_elements')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('system_module_elements')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function supplierCreditFacilitySettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('supplier_credit_facility_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('supplier_credit_facility_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('supplier_credit_facility_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function systemModuleConfigurations($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('system_module_configurations');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('system_module_configurations')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('system_module_configurations')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryRejectedMaterials($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_site_diary_rejected_materials');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_site_diary_rejected_materials')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_site_diary_rejected_materials')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryVisitors($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_site_diary_visitors');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_site_diary_visitors')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_site_diary_visitors')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function structuredDocuments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('structured_documents');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('structured_documents')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('structured_documents')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function systemSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('system_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('system_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('system_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationResponseLog($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('technical_evaluation_response_log');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('technical_evaluation_response_log')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('technical_evaluation_response_log')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluations($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('technical_evaluations');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('technical_evaluations')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('technical_evaluations')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function templateTenderDocumentFolders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('template_tender_document_folders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('template_tender_document_folders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('template_tender_document_folders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderDocumentDownloadLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_document_download_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_document_download_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_document_download_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderCallingTenderInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_calling_tender_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_calling_tender_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_calling_tender_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tags($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tags');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tags')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tags')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationSetReferences($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('technical_evaluation_set_references');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('technical_evaluation_set_references')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('technical_evaluation_set_references')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationAttachments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('technical_evaluation_attachments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('technical_evaluation_attachments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('technical_evaluation_attachments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationItems($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('technical_evaluation_items');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('technical_evaluation_items')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('technical_evaluation_items')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationTendererOptions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('technical_evaluation_tenderer_options');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('technical_evaluation_tenderer_options')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('technical_evaluation_tenderer_options')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationVerifierLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('technical_evaluation_verifier_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('technical_evaluation_verifier_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('technical_evaluation_verifier_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function templateTenderDocumentFolderWorkCategory($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('template_tender_document_folder_work_category');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('template_tender_document_folder_work_category')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('template_tender_document_folder_work_category')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function templateTenderDocumentFiles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('template_tender_document_files');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('template_tender_document_files')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('template_tender_document_files')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function templateTenderDocumentFilesRolesReadonly($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('template_tender_document_files_roles_readonly');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('template_tender_document_files_roles_readonly')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('template_tender_document_files_roles_readonly')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderAlternativesPosition($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_alternatives_position');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_alternatives_position')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_alternatives_position')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderCallingTenderInformationUser($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_calling_tender_information_user');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_calling_tender_information_user')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_calling_tender_information_user')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderInterviewInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_interview_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_interview_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_interview_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderLotInformationUser($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_lot_information_user');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_lot_information_user')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_lot_information_user')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderRotInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_rot_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_rot_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_rot_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderRotInformationUser($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_rot_information_user');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_rot_information_user')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_rot_information_user')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderDocumentFiles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_document_files');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_document_files')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_document_files')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderLotInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_lot_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_lot_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_lot_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderDocumentFilesRolesReadonly($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_document_files_roles_readonly');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_document_files_roles_readonly')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_document_files_roles_readonly')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderDocumentFolders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_document_folders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_document_folders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_document_folders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderFormVerifierLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_form_verifier_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_form_verifier_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_form_verifier_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderInterviews($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_interviews');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_interviews')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_interviews')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderReminders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_reminders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_reminders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_reminders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderUserTechnicalEvaluationVerifier($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_user_technical_evaluation_verifier');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_user_technical_evaluation_verifier')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_user_technical_evaluation_verifier')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderUserVerifierOpenTender($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_user_verifier_open_tender');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_user_verifier_open_tender')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_user_verifier_open_tender')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderUserVerifierRetender($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_user_verifier_retender');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_user_verifier_retender')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_user_verifier_retender')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tendererTechnicalEvaluationInformation($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tenderer_technical_evaluation_information');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tenderer_technical_evaluation_information')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tenderer_technical_evaluation_information')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tendererTechnicalEvaluationInformationLog($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tenderer_technical_evaluation_information_log');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tenderer_technical_evaluation_information_log')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tenderer_technical_evaluation_information_log')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function themeSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('theme_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('theme_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('theme_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function users($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('users');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('users')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('users')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function userCompanyLog($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('user_company_log');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('user_company_log')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('user_company_log')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function trackRecordProjects($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('track_record_projects');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('track_record_projects')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('track_record_projects')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorDetailSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_detail_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_detail_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_detail_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenders($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tenders');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tenders')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tenders')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function uploads($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('uploads');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('uploads')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('uploads')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function userLogins($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('user_logins');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('user_logins')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('user_logins')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function userSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('user_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('user_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('user_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function usersCompanyVerificationPrivileges($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('users_company_verification_privileges');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('users_company_verification_privileges')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('users_company_verification_privileges')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorCategoryTemporaryRecords($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_category_temporary_records');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_category_temporary_records')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_category_temporary_records')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorCategoryVendorWorkCategory($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_category_vendor_work_category');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_category_vendor_work_category')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_category_vendor_work_category')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorEvaluationCycleScores($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_evaluation_cycle_scores');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_evaluation_cycle_scores')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_evaluation_cycle_scores')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorEvaluationScores($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_evaluation_scores');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_evaluation_scores')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_evaluation_scores')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorManagementInstructionSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_management_instruction_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_management_instruction_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_management_instruction_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationFormChangeLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_form_change_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_form_change_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_form_change_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorManagementGradeLevels($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_management_grade_levels');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_management_grade_levels')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_management_grade_levels')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationModuleParameters($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_module_parameters');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_module_parameters')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_module_parameters')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationProjectRemovalReasons($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_project_removal_reasons');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_project_removal_reasons')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_project_removal_reasons')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationSubmissionReminderSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_submission_reminder_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_submission_reminder_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_submission_reminder_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorManagementGrades($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_management_grades');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_management_grades')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_management_grades')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorManagementUserPermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_management_user_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_management_user_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_management_user_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationCompanyFormEvaluationLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_company_form_evaluation_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_company_form_evaluation_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_company_form_evaluation_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationCompanyForms($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_company_forms');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_company_forms')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_company_forms')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationFormChangeRequests($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_form_change_requests');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_form_change_requests')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_form_change_requests')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationProcessorEditDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_processor_edit_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_processor_edit_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_processor_edit_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationProcessorEditLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_processor_edit_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_processor_edit_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_processor_edit_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationRemovalRequests($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_removal_requests');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_removal_requests')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_removal_requests')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationSetups($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_setups');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_setups')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_setups')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationTemplateForms($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_template_forms');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_template_forms')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_template_forms')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorProfileModuleParameters($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_profile_module_parameters');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_profile_module_parameters')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_profile_module_parameters')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorProfiles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_profiles');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_profiles')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_profiles')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationAndPrequalificationModuleParameters($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_registration_and_prequalification_module_parameters');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_registration_and_prequalification_module_parameters')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_registration_and_prequalification_module_parameters')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationFormTemplateMappings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_registration_form_template_mappings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_registration_form_template_mappings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_registration_form_template_mappings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationSections($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_registration_sections');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_registration_sections')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_registration_sections')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationSubmissionLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_registration_submission_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_registration_submission_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_registration_submission_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluations($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluations');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluations')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluations')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluators($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluators');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluators')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluators')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPreQualificationSetups($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_pre_qualification_setups');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_pre_qualification_setups')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_pre_qualification_setups')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPreQualificationTemplateForms($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_pre_qualification_template_forms');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_pre_qualification_template_forms')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_pre_qualification_template_forms')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPreQualificationVendorGroupGrades($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_pre_qualification_vendor_group_grades');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_pre_qualification_vendor_group_grades')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_pre_qualification_vendor_group_grades')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPreQualifications($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_pre_qualifications');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_pre_qualifications')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_pre_qualifications')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorProfileRemarks($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_profile_remarks');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_profile_remarks')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_profile_remarks')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationPayments($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_registration_payments');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_registration_payments')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_registration_payments')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationProcessors($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_registration_processors');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_registration_processors')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_registration_processors')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weatherRecordReports($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('weather_record_reports');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('weather_record_reports')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('weather_record_reports')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorTypeChangeLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_type_change_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_type_change_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_type_change_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weightedNodeScores($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('weighted_node_scores');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('weighted_node_scores')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('weighted_node_scores')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function workCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('work_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('work_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('work_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function workSubcategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('work_subcategories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('work_subcategories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('work_subcategories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weathers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('weathers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('weathers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('weathers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorWorkCategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_work_categories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_work_categories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_work_categories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorWorkSubcategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_work_subcategories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_work_subcategories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_work_subcategories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weightedNodes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('weighted_nodes');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('weighted_nodes')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('weighted_nodes')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendors($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendors');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendors')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendors')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorWorkCategoryWorkCategory($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_work_category_work_category');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_work_category_work_category')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_work_category_work_category')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function verifiers($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('verifiers');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('verifiers')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('verifiers')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weatherRecords($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('weather_records');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('weather_records')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('weather_records')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function accessLog($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('access_log');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('access_log')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('access_log')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projects($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('projects');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('projects')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('projects')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function accountingReportExportLogDetails($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('accounting_report_export_log_details');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('accounting_report_export_log_details')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('accounting_report_export_log_details')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function interimClaims($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('interim_claims');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('interim_claims')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('interim_claims')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function architectInstructions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('architect_instructions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('architect_instructions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('architect_instructions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeSecondLevelMessages($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('ae_second_level_messages');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('ae_second_level_messages')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('ae_second_level_messages')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companies($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('companies');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('companies')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('companies')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function countries($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('countries');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('countries')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('countries')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function states($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('states');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('states')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('states')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function clauseItems($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('clause_items');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('clause_items')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('clause_items')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementAttachmentSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_attachment_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_attachment_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_attachment_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementOpenRfp($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_open_rfp');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_open_rfp')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_open_rfp')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionA($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_approval_document_section_a');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_approval_document_section_a')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_approval_document_section_a')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrations($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_registrations');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_registrations')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_registrations')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyPersonnel($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('company_personnel');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('company_personnel')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('company_personnel')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCompanyRoles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('consultant_management_company_roles');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('consultant_management_company_roles')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('consultant_management_company_roles')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function productTypes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('product_types');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('product_types')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('product_types')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function developmentTypes($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('development_types');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('development_types')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('development_types')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorWorkCategory($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('contractor_work_category');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('contractor_work_category')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('contractor_work_category')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function previousCpeGrades($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('previous_cpe_grades');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('previous_cpe_grades')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('previous_cpe_grades')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dailyLabourReportLabourRates($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('daily_labour_report_labour_rates');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('daily_labour_report_labour_rates')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('daily_labour_report_labour_rates')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailNotificationRecipients($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('email_notification_recipients');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('email_notification_recipients')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('email_notification_recipients')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationClientModules($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('external_application_client_modules');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('external_application_client_modules')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('external_application_client_modules')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formColumnSections($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('form_column_sections');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('form_column_sections')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('form_column_sections')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumThreadUserSettings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('forum_thread_user_settings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('forum_thread_user_settings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('forum_thread_user_settings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractEarlyWarnings($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('indonesia_civil_contract_early_warnings');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('indonesia_civil_contract_early_warnings')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('indonesia_civil_contract_early_warnings')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractEwEot($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('indonesia_civil_contract_ew_eot');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('indonesia_civil_contract_ew_eot')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('indonesia_civil_contract_ew_eot')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardClauseCommentReadLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('letter_of_award_clause_comment_read_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('letter_of_award_clause_comment_read_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('letter_of_award_clause_comment_read_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function menus($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('menus');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('menus')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('menus')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementDefects($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('site_management_defects');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('site_management_defects')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('site_management_defects')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function modulePermissionSubsidiaries($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('module_permission_subsidiaries');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('module_permission_subsidiaries')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('module_permission_subsidiaries')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function notificationsCategoriesInGroups($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('notifications_categories_in_groups');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('notifications_categories_in_groups')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('notifications_categories_in_groups')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectPermissions($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('object_permissions');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('object_permissions')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('object_permissions')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationReportEditLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('open_tender_award_recommendation_report_edit_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('open_tender_award_recommendation_report_edit_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('open_tender_award_recommendation_report_edit_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function structuredDocumentClauses($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('structured_document_clauses');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('structured_document_clauses')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('structured_document_clauses')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationAttachmentListItems($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('technical_evaluation_attachment_list_items');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('technical_evaluation_attachment_list_items')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('technical_evaluation_attachment_list_items')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderInterviewLogs($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('tender_interview_logs');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('tender_interview_logs')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('tender_interview_logs')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function trackRecordProjectVendorWorkSubcategories($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('track_record_project_vendor_work_subcategories');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('track_record_project_vendor_work_subcategories')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('track_record_project_vendor_work_subcategories')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationCycles($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_performance_evaluation_cycles');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_performance_evaluation_cycles')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_performance_evaluation_cycles')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationProcessorRemarks($id)
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('vendor_registration_processor_remarks');
        $msg = 'Data updated successfully';
        $success = true;
        $httpResponseCode = 200;
        $records = \Input::all();

        try {
            // Check if the record exists
            $recordExists = DB::table('vendor_registration_processor_remarks')->find($id);

            if (!$recordExists) {
                throw new \Exception('Record not found');
            }

            // Prepare data for update by filtering out invalid columns
            $dataToUpdate = array_intersect_key($records, array_flip($columns));

            // Check if there's any data to update
            if (empty($dataToUpdate)) {
                throw new \Exception('No valid columns provided for update');
            }

            // Update the record
            DB::table('vendor_registration_processor_remarks')->where('id', $id)->update($dataToUpdate);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
        
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }
}
?>