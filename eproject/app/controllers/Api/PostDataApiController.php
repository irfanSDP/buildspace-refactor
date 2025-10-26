<?php
namespace Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use PCK\Users\User;
use PCK\Users\LmsUser;
use PCK\Users\LmsApiLog;

class PostDataApiController extends \BaseController
{
    protected $expectedToken = 'omkoFF3J2J6XywgbZF81Si5AK7uJNza6yos0FnrL5RdnTkLacsKS60LxcFxe6mPR';

    public function insertLmsUser()
    {
        $input = \Request::all();

        $response = [];

        try{
            $user = User::where("email", $input["email"])->first();

            if(!$user)
            {
                $response = ['message' => 'User not found'];

                LmsApiLog::create(["api_response" => json_encode($response)]);

                return Response::json($response, 404);
            }

            $lmsUser = LmsUser::where("user_id", $user->id)
                                ->where("lms_course_id", $input["lms_course_id"])
                                ->first();

            unset($input["email"]);

            $data["user_id"]                 = $user->id;
            $data["lms_course_id"]           = isset($input["lms_course_id"])? $input["lms_course_id"] : NULL;
            $data["lms_course_name"]         = isset($input["lms_course_name"]) ? $input["lms_course_name"] : NULL;
            $data["lms_course_score"]        = isset($input["lms_course_score"]) ? $input["lms_course_score"] : NULL;
            $data["lms_course_completed"]    = isset($input["lms_course_completed"]) ? $input["lms_course_completed"] : false;
            $data["lms_course_completed_at"] = isset($input["lms_course_completed_at"]) ? date('d-m-Y', strtotime($input["lms_course_completed_at"])) : NULL;

            if($lmsUser)
            {
                LmsUser::where("user_id", $user->id)
                        ->where("lms_course_id", $input["lms_course_id"])
                        ->update($data);
            }
            else
            {
                $lmsUser = LmsUser::create($data);
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();

            $response = [
                'success' => false,
                'error' => $error
            ];

            LmsApiLog::create(["api_response" => json_encode($response)]);

            return Response::json($response, 200);
        }

        $response = [
            'success' => true,
            'data' => $data
        ];

        LmsApiLog::create(["api_response" => json_encode($response)]);

        return Response::json($response, 200);
    }
    
    public function accountCodeSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('account_code_settings')->insert([
            'project_id' => $records['project_id'],
            'apportionment_type_id' => $records['apportionment_type_id'],
            'account_group_id' => $records['account_group_id'],
            'submitted_for_approval_by' => $records['submitted_for_approval_by'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'vendor_category_id' => $records['vendor_category_id'],
            'beneficiary_bank_account_number' => $records['beneficiary_bank_account_number'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function additionalElementValues()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('additional_element_values')->insert([
            'element_value_id' => $records['element_value_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'value' => $records['value'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function additionalExpenses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('additional_expenses')->insert([
            'project_id' => $records['project_id'],
            'architect_instruction_id' => $records['architect_instruction_id'],
            'created_by' => $records['created_by'],
            'commencement_date_of_event' => $records['commencement_date_of_event'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'initial_estimate_of_claim' => $records['initial_estimate_of_claim'],
            'amount_claimed' => $records['amount_claimed'],
            'amount_granted' => $records['amount_granted'],
            'status' => $records['status'],
            'subject' => $records['subject'],
            'detailed_elaborations' => $records['detailed_elaborations'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function additionalExpenseInterimClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('additional_expense_interim_claims')->insert([
            'additional_expense_id' => $records['additional_expense_id'],
            'interim_claim_id' => $records['interim_claim_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeContractorConfirmDelays()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('ae_contractor_confirm_delays')->insert([
            'additional_expense_id' => $records['additional_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'date_on_which_delay_is_over' => $records['date_on_which_delay_is_over'],
            'deadline_to_submit_final_claim' => $records['deadline_to_submit_final_claim'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeFirstLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('ae_first_level_messages')->insert([
            'additional_expense_id' => $records['additional_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'decision' => $records['decision'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'details' => $records['details'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeFourthLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('ae_fourth_level_messages')->insert([
            'updated_at' => $records['updated_at'],
            'additional_expense_id' => $records['additional_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'grant_different_amount' => $records['grant_different_amount'],
            'decision' => $records['decision'],
            'type' => $records['type'],
            'locked' => $records['locked'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function accountingReportExportLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('accounting_report_export_logs')->insert([
            'claim_certificate_id' => $records['claim_certificate_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function accountingReportExportLogItemCodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('accounting_report_export_log_item_codes')->insert([
            'accounting_report_export_log_detail_id' => $records['accounting_report_export_log_detail_id'],
            'item_code_setting_id' => $records['item_code_setting_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function acknowledgementLetters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('acknowledgement_letters')->insert([
            'tender_id' => $records['tender_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'enable_letter' => $records['enable_letter'],
            'letter_content' => $records['letter_content'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function additionalExpenseClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('additional_expense_claims')->insert([
            'additional_expense_id' => $records['additional_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'final_claim_amount' => $records['final_claim_amount'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeThirdLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('ae_third_level_messages')->insert([
            'additional_expense_id' => $records['additional_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deadline_to_comply_with' => $records['deadline_to_comply_with'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aiThirdLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('ai_third_level_messages')->insert([
            'updated_at' => $records['updated_at'],
            'architect_instruction_id' => $records['architect_instruction_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'compliance_date' => $records['compliance_date'],
            'compliance_status' => $records['compliance_status'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'reason' => $records['reason'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function architectInstructionEngineerInstruction()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('architect_instruction_engineer_instruction')->insert([
            'architect_instruction_id' => $records['architect_instruction_id'],
            'engineer_instruction_id' => $records['engineer_instruction_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function apportionmentTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('apportionment_types')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function architectInstructionInterimClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('architect_instruction_interim_claims')->insert([
            'architect_instruction_id' => $records['architect_instruction_id'],
            'interim_claim_id' => $records['interim_claim_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'subject' => $records['subject'],
            'letter_to_contractor' => $records['letter_to_contractor'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function architectInstructionMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('architect_instruction_messages')->insert([
            'architect_instruction_id' => $records['architect_instruction_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'reason' => $records['reason'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function attachedClauseItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('attached_clause_items')->insert([
            'attachable_id' => $records['attachable_id'],
            'origin_id' => $records['origin_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'priority' => $records['priority'],
            'attachable_type' => $records['attachable_type'],
            'no' => $records['no'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function calendarSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('calendar_settings')->insert([
            'country_id' => $records['country_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificateEmailLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('claim_certificate_email_logs')->insert([
            'claim_certificate_id' => $records['claim_certificate_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function cidbGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('cidb_grades')->insert([
            'parent_id' => $records['parent_id'],
            'disabled' => $records['disabled'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'grade' => $records['grade'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function assignCompaniesLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('assign_companies_logs')->insert([
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function assignCompanyInDetailLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('assign_company_in_detail_logs')->insert([
            'assign_company_log_id' => $records['assign_company_log_id'],
            'contract_group_id' => $records['contract_group_id'],
            'company_id' => $records['company_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function authenticationLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('authentication_logs')->insert([
            'user_id' => $records['user_id'],
            'login_at' => $records['login_at'],
            'logout_at' => $records['logout_at'],
            'ip_address' => $records['ip_address'],
            'user_agent' => $records['user_agent'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function calendars()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('calendars')->insert([
            'updated_at' => $records['updated_at'],
            'country_id' => $records['country_id'],
            'state_id' => $records['state_id'],
            'created_at' => $records['created_at'],
            'start_date' => $records['start_date'],
            'end_date' => $records['end_date'],
            'event_type' => $records['event_type'],
            'description' => $records['description'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificateInvoiceInformationUpdateLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('claim_certificate_invoice_information_update_logs')->insert([
            'claim_certificate_invoice_information_id' => $records['claim_certificate_invoice_information_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function buildingInformationModellingLevels()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('building_information_modelling_levels')->insert([
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function businessEntityTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('business_entity_types')->insert([
            'hidden' => $records['hidden'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function cidbCodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('cidb_codes')->insert([
            'parent_id' => $records['parent_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'disabled' => $records['disabled'],
            'code' => $records['code'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyCidbCode()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_cidb_code')->insert([
            'company_id' => $records['company_id'],
            'cidb_code_id' => $records['cidb_code_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificatePaymentNotificationLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('claim_certificate_payment_notification_logs')->insert([
            'claim_certificate_id' => $records['claim_certificate_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyDetailAttachmentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_detail_attachment_settings')->insert([
            'name_attachments' => $records['name_attachments'],
            'address_attachments' => $records['address_attachments'],
            'contract_group_category_attachments' => $records['contract_group_category_attachments'],
            'vendor_category_attachments' => $records['vendor_category_attachments'],
            'main_contact_attachments' => $records['main_contact_attachments'],
            'reference_number_attachments' => $records['reference_number_attachments'],
            'tax_registration_number_attachments' => $records['tax_registration_number_attachments'],
            'email_attachments' => $records['email_attachments'],
            'telephone_attachments' => $records['telephone_attachments'],
            'fax_attachments' => $records['fax_attachments'],
            'country_attachments' => $records['country_attachments'],
            'state_attachments' => $records['state_attachments'],
            'company_status_attachments' => $records['company_status_attachments'],
            'bumiputera_equity_attachments' => $records['bumiputera_equity_attachments'],
            'non_bumiputera_equity_attachments' => $records['non_bumiputera_equity_attachments'],
            'foreigner_equity_attachments' => $records['foreigner_equity_attachments'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'cidb_grade_attachments' => $records['cidb_grade_attachments'],
            'bim_level_attachments' => $records['bim_level_attachments'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificatePayments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('claim_certificate_payments')->insert([
            'notification_sent' => $records['notification_sent'],
            'claim_certificate_id' => $records['claim_certificate_id'],
            'updated_at' => $records['updated_at'],
            'amount' => $records['amount'],
            'date' => $records['date'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'bank' => $records['bank'],
            'reference' => $records['reference'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function claimCertificatePrintLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('claim_certificate_print_logs')->insert([
            'claim_certificate_id' => $records['claim_certificate_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function clauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('clauses')->insert([
            'contract_id' => $records['contract_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'type' => $records['type'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyImportedUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_imported_users')->insert([
            'company_id' => $records['company_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyImportedUsersLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_imported_users_log')->insert([
            'company_id' => $records['company_id'],
            'user_id' => $records['user_id'],
            'created_by' => $records['created_by'],
            'import' => $records['import'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyPersonnelSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_personnel_settings')->insert([
            'has_attachments' => $records['has_attachments'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTenderCallingTenderInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_tender_calling_tender_information')->insert([
            'company_id' => $records['company_id'],
            'tender_calling_tender_information_id' => $records['tender_calling_tender_information_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTenderLotInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_tender_lot_information')->insert([
            'company_id' => $records['company_id'],
            'tender_lot_information_id' => $records['tender_lot_information_id'],
            'added_by_gcd' => $records['added_by_gcd'],
            'status' => $records['status'],
            'deleted_at' => $records['deleted_at'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTenderRotInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_tender_rot_information')->insert([
            'status' => $records['status'],
            'company_id' => $records['company_id'],
            'tender_rot_information_id' => $records['tender_rot_information_id'],
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTenderTenderAlternatives()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_tender_tender_alternatives')->insert([
            'updated_at' => $records['updated_at'],
            'tender_alternative_id' => $records['tender_alternative_id'],
            'tender_amount' => $records['tender_amount'],
            'other_bill_type_amount_except_prime_cost_provisional' => $records['other_bill_type_amount_except_prime_cost_provisional'],
            'supply_of_material_amount' => $records['supply_of_material_amount'],
            'original_tender_amount' => $records['original_tender_amount'],
            'discounted_percentage' => $records['discounted_percentage'],
            'discounted_amount' => $records['discounted_amount'],
            'completion_period' => $records['completion_period'],
            'contractor_adjustment_amount' => $records['contractor_adjustment_amount'],
            'contractor_adjustment_percentage' => $records['contractor_adjustment_percentage'],
            'earnest_money' => $records['earnest_money'],
            'company_tender_id' => $records['company_tender_id'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyProject()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_project')->insert([
            'project_id' => $records['project_id'],
            'company_id' => $records['company_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'contract_group_id' => $records['contract_group_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyPropertyDevelopers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_property_developers')->insert([
            'company_id' => $records['company_id'],
            'property_developer_id' => $records['property_developer_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTemporaryDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_temporary_details')->insert([
            'state_id' => $records['state_id'],
            'vendor_registration_id' => $records['vendor_registration_id'],
            'foreigner_equity' => $records['foreigner_equity'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'company_status' => $records['company_status'],
            'cidb_grade' => $records['cidb_grade'],
            'bim_level_id' => $records['bim_level_id'],
            'country_id' => $records['country_id'],
            'is_bumiputera' => $records['is_bumiputera'],
            'bumiputera_equity' => $records['bumiputera_equity'],
            'non_bumiputera_equity' => $records['non_bumiputera_equity'],
            'address' => $records['address'],
            'main_contact' => $records['main_contact'],
            'tax_registration_no' => $records['tax_registration_no'],
            'email' => $records['email'],
            'telephone_number' => $records['telephone_number'],
            'fax_number' => $records['fax_number'],
            'reference_no' => $records['reference_no'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyTender()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_tender')->insert([
            'contractor_adjustment_amount' => $records['contractor_adjustment_amount'],
            'company_id' => $records['company_id'],
            'tender_id' => $records['tender_id'],
            'discounted_amount' => $records['discounted_amount'],
            'earnest_money' => $records['earnest_money'],
            'completion_period' => $records['completion_period'],
            'submitted' => $records['submitted'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'can_login' => $records['can_login'],
            'selected_contractor' => $records['selected_contractor'],
            'submitted_at' => $records['submitted_at'],
            'supply_of_material_amount' => $records['supply_of_material_amount'],
            'other_bill_type_amount_except_prime_cost_provisional' => $records['other_bill_type_amount_except_prime_cost_provisional'],
            'contractor_adjustment_percentage' => $records['contractor_adjustment_percentage'],
            'original_tender_amount' => $records['original_tender_amount'],
            'discounted_percentage' => $records['discounted_percentage'],
            'rates' => $records['rates'],
            'tender_amount' => $records['tender_amount'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyVendorCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_vendor_category')->insert([
            'company_id' => $records['company_id'],
            'vendor_category_id' => $records['vendor_category_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionE()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_approval_document_section_e')->insert([
            'consultant_management_approval_document_id' => $records['consultant_management_approval_document_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_attachments')->insert([
            'consultant_management_attachment_setting_id' => $records['consultant_management_attachment_setting_id'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'company_id' => $records['company_id'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_questionnaires')->insert([
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'company_id' => $records['company_id'],
            'status' => $records['status'],
            'published_date' => $records['published_date'],
            'unpublished_date' => $records['unpublished_date'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCallingRfp()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_calling_rfp')->insert([
            'consultant_management_rfp_revision_id' => $records['consultant_management_rfp_revision_id'],
            'calling_rfp_date' => $records['calling_rfp_date'],
            'closing_rfp_date' => $records['closing_rfp_date'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'is_extend' => $records['is_extend'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCompanyRoleLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_company_role_logs')->insert([
            'role' => $records['role'],
            'consultant_management_contract_id' => $records['consultant_management_contract_id'],
            'company_id' => $records['company_id'],
            'calling_rfp' => $records['calling_rfp'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocuments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_approval_documents')->insert([
            'updated_at' => $records['updated_at'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'document_reference_no' => $records['document_reference_no'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionAppendix()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_approval_document_section_appendix')->insert([
            'consultant_management_approval_document_id' => $records['consultant_management_approval_document_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionC()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_approval_document_section_c')->insert([
            'consultant_management_approval_document_id' => $records['consultant_management_approval_document_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionD()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_approval_document_section_d')->insert([
            'consultant_management_approval_document_id' => $records['consultant_management_approval_document_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionB()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_approval_document_section_b')->insert([
            'created_by' => $records['created_by'],
            'consultant_management_approval_document_id' => $records['consultant_management_approval_document_id'],
            'updated_at' => $records['updated_at'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'project_brief' => $records['project_brief'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_approval_document_verifiers')->insert([
            'consultant_management_approval_document_id' => $records['consultant_management_approval_document_id'],
            'user_id' => $records['user_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_approval_document_verifier_versions')->insert([
            'consultant_management_approval_document_verifier_id' => $records['consultant_management_approval_document_verifier_id'],
            'user_id' => $records['user_id'],
            'version' => $records['version'],
            'status' => $records['status'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantQuestionnaireReplies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_questionnaire_replies')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_questionnaire_id' => $records['consultant_management_questionnaire_id'],
            'consultant_management_consultant_questionnaire_id' => $records['consultant_management_consultant_questionnaire_id'],
            'consultant_management_questionnaire_option_id' => $records['consultant_management_questionnaire_option_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'text' => $records['text'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCallingRfpVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_calling_rfp_verifiers')->insert([
            'consultant_management_calling_rfp_id' => $records['consultant_management_calling_rfp_id'],
            'user_id' => $records['user_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCallingRfpCompanies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_calling_rfp_companies')->insert([
            'consultant_management_calling_rfp_id' => $records['consultant_management_calling_rfp_id'],
            'company_id' => $records['company_id'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCallRfpVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_call_rfp_verifier_versions')->insert([
            'consultant_management_calling_rfp_verifier_id' => $records['consultant_management_calling_rfp_verifier_id'],
            'user_id' => $records['user_id'],
            'version' => $records['version'],
            'status' => $records['status'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpQuestionnaireReplies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_rfp_questionnaire_replies')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_rfp_questionnaire_id' => $records['consultant_management_rfp_questionnaire_id'],
            'consultant_management_consultant_questionnaire_id' => $records['consultant_management_consultant_questionnaire_id'],
            'consultant_management_rfp_questionnaire_option_id' => $records['consultant_management_rfp_questionnaire_option_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'text' => $records['text'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfp()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_rfp')->insert([
            'consultant_management_rfp_revision_id' => $records['consultant_management_rfp_revision_id'],
            'company_id' => $records['company_id'],
            'awarded' => $records['awarded'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpReplyAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_rfp_reply_attachments')->insert([
            'consultant_management_rfp_questionnaire_id' => $records['consultant_management_rfp_questionnaire_id'],
            'consultant_management_consultant_questionnaire_id' => $records['consultant_management_consultant_questionnaire_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpCommonInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_rfp_common_information')->insert([
            'consultant_management_consultant_rfp_id' => $records['consultant_management_consultant_rfp_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'contact_email' => $records['contact_email'],
            'name_in_loa' => $records['name_in_loa'],
            'remarks' => $records['remarks'],
            'contact_name' => $records['contact_name'],
            'contact_number' => $records['contact_number'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_letter_of_award_clauses')->insert([
            'updated_at' => $records['updated_at'],
            'template_id' => $records['template_id'],
            'display_numbering' => $records['display_numbering'],
            'sequence_number' => $records['sequence_number'],
            'parent_id' => $records['parent_id'],
            'created_at' => $records['created_at'],
            'content' => $records['content'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardTemplateClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_letter_of_award_template_clauses')->insert([
            'updated_at' => $records['updated_at'],
            'template_id' => $records['template_id'],
            'display_numbering' => $records['display_numbering'],
            'sequence_number' => $records['sequence_number'],
            'parent_id' => $records['parent_id'],
            'created_at' => $records['created_at'],
            'content' => $records['content'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementContracts()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_contracts')->insert([
            'updated_at' => $records['updated_at'],
            'subsidiary_id' => $records['subsidiary_id'],
            'country_id' => $records['country_id'],
            'state_id' => $records['state_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'reference_no' => $records['reference_no'],
            'title' => $records['title'],
            'description' => $records['description'],
            'address' => $records['address'],
            'modified_currency_code' => $records['modified_currency_code'],
            'modified_currency_name' => $records['modified_currency_name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpProposedFees()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_rfp_proposed_fees')->insert([
            'consultant_management_consultant_rfp_id' => $records['consultant_management_consultant_rfp_id'],
            'consultant_management_subsidiary_id' => $records['consultant_management_subsidiary_id'],
            'proposed_fee_percentage' => $records['proposed_fee_percentage'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'proposed_fee_amount' => $records['proposed_fee_amount'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_users')->insert([
            'user_id' => $records['user_id'],
            'is_admin' => $records['is_admin'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementExcludeAttachmentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_exclude_attachment_settings')->insert([
            'consultant_management_attachment_setting_id' => $records['consultant_management_attachment_setting_id'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantReplyAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_reply_attachments')->insert([
            'consultant_management_questionnaire_id' => $records['consultant_management_questionnaire_id'],
            'consultant_management_consultant_questionnaire_id' => $records['consultant_management_consultant_questionnaire_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementConsultantRfpAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_consultant_rfp_attachments')->insert([
            'consultant_management_rfp_attachment_setting_id' => $records['consultant_management_rfp_attachment_setting_id'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'company_id' => $records['company_id'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementExcludeQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_exclude_questionnaires')->insert([
            'consultant_management_questionnaire_id' => $records['consultant_management_questionnaire_id'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'company_id' => $records['company_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_letter_of_award_attachments')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_letter_of_award_id' => $records['consultant_management_letter_of_award_id'],
            'created_at' => $records['created_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'title' => $records['title'],
            'attachment_filename' => $records['attachment_filename'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementListOfConsultants()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_list_of_consultants')->insert([
            'consultant_management_rfp_revision_id' => $records['consultant_management_rfp_revision_id'],
            'proposed_fee' => $records['proposed_fee'],
            'calling_rfp_date' => $records['calling_rfp_date'],
            'closing_rfp_date' => $records['closing_rfp_date'],
            'updated_at' => $records['updated_at'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardTemplates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_letter_of_award_templates')->insert([
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'title' => $records['title'],
            'letterhead' => $records['letterhead'],
            'signatory' => $records['signatory'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementListOfConsultantVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_list_of_consultant_verifiers')->insert([
            'consultant_management_list_of_consultant_id' => $records['consultant_management_list_of_consultant_id'],
            'user_id' => $records['user_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementProductTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_product_types')->insert([
            'consultant_management_subsidiary_id' => $records['consultant_management_subsidiary_id'],
            'product_type_id' => $records['product_type_id'],
            'number_of_unit' => $records['number_of_unit'],
            'lot_dimension_length' => $records['lot_dimension_length'],
            'lot_dimension_width' => $records['lot_dimension_width'],
            'proposed_built_up_area' => $records['proposed_built_up_area'],
            'proposed_average_selling_price' => $records['proposed_average_selling_price'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLoaSubsidiaryRunningNumbers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_loa_subsidiary_running_numbers')->insert([
            'subsidiary_id' => $records['subsidiary_id'],
            'next_running_number' => $records['next_running_number'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_questionnaires')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_contract_id' => $records['consultant_management_contract_id'],
            'created_at' => $records['created_at'],
            'required' => $records['required'],
            'with_attachment' => $records['with_attachment'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'question' => $records['question'],
            'type' => $records['type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwards()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_letter_of_awards')->insert([
            'running_number' => $records['running_number'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'signatory' => $records['signatory'],
            'reference_number' => $records['reference_number'],
            'letterhead' => $records['letterhead'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementOpenRfpVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_open_rfp_verifiers')->insert([
            'consultant_management_open_rfp_id' => $records['consultant_management_open_rfp_id'],
            'user_id' => $records['user_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementOpenRfpVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_open_rfp_verifier_versions')->insert([
            'consultant_management_open_rfp_verifier_id' => $records['consultant_management_open_rfp_verifier_id'],
            'user_id' => $records['user_id'],
            'version' => $records['version'],
            'status' => $records['status'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementQuestionnaireOptions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_questionnaire_options')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_questionnaire_id' => $records['consultant_management_questionnaire_id'],
            'created_at' => $records['created_at'],
            'order' => $records['order'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'text' => $records['text'],
            'value' => $records['value'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_letter_of_award_verifiers')->insert([
            'consultant_management_letter_of_award_id' => $records['consultant_management_letter_of_award_id'],
            'user_id' => $records['user_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLetterOfAwardVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_letter_of_award_verifier_versions')->insert([
            'consultant_management_letter_of_award_verifier_id' => $records['consultant_management_letter_of_award_verifier_id'],
            'user_id' => $records['user_id'],
            'version' => $records['version'],
            'status' => $records['status'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementListOfConsultantCompanies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_list_of_consultant_companies')->insert([
            'consultant_management_list_of_consultant_id' => $records['consultant_management_list_of_consultant_id'],
            'company_id' => $records['company_id'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementLocVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_loc_verifier_versions')->insert([
            'consultant_management_list_of_consultant_verifier_id' => $records['consultant_management_list_of_consultant_verifier_id'],
            'user_id' => $records['user_id'],
            'version' => $records['version'],
            'status' => $records['status'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRecommendationOfConsultantCompanies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_recommendation_of_consultant_companies')->insert([
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'company_id' => $records['company_id'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRecommendationOfConsultantVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_recommendation_of_consultant_verifiers')->insert([
            'consultant_management_recommendation_of_consultant_id' => $records['consultant_management_recommendation_of_consultant_id'],
            'user_id' => $records['user_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpResubmissionVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_resubmission_verifiers')->insert([
            'consultant_management_open_rfp_id' => $records['consultant_management_open_rfp_id'],
            'user_id' => $records['user_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpInterviewTokens()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_interview_tokens')->insert([
            'consultant_management_rfp_interview_consultant_id' => $records['consultant_management_rfp_interview_consultant_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'token' => $records['token'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpInterviewConsultants()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_interview_consultants')->insert([
            'consultant_management_rfp_interview_id' => $records['consultant_management_rfp_interview_id'],
            'company_id' => $records['company_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'interview_timestamp' => $records['interview_timestamp'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'remarks' => $records['remarks'],
            'consultant_remarks' => $records['consultant_remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_questionnaires')->insert([
            'updated_at' => $records['updated_at'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'company_id' => $records['company_id'],
            'created_at' => $records['created_at'],
            'required' => $records['required'],
            'with_attachment' => $records['with_attachment'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'question' => $records['question'],
            'type' => $records['type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpInterviews()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_interviews')->insert([
            'updated_at' => $records['updated_at'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'created_at' => $records['created_at'],
            'interview_date' => $records['interview_date'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'title' => $records['title'],
            'details' => $records['details'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpQuestionnaireOptions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_questionnaire_options')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_rfp_questionnaire_id' => $records['consultant_management_rfp_questionnaire_id'],
            'created_at' => $records['created_at'],
            'order' => $records['order'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'text' => $records['text'],
            'value' => $records['value'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRolesContractGroupCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_roles_contract_group_categories')->insert([
            'role' => $records['role'],
            'contract_group_category_id' => $records['contract_group_category_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpRevisions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_revisions')->insert([
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'revision' => $records['revision'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpAttachmentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_attachment_settings')->insert([
            'updated_at' => $records['updated_at'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'mandatory' => $records['mandatory'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'title' => $records['title'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpDocuments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_documents')->insert([
            'created_by' => $records['created_by'],
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'updated_at' => $records['updated_at'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRfpResubmissionVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_rfp_resubmission_verifier_versions')->insert([
            'consultant_management_rfp_resubmission_verifier_id' => $records['consultant_management_rfp_resubmission_verifier_id'],
            'user_id' => $records['user_id'],
            'version' => $records['version'],
            'status' => $records['status'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRecommendationOfConsultants()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_recommendation_of_consultants')->insert([
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'proposed_fee' => $records['proposed_fee'],
            'calling_rfp_proposed_date' => $records['calling_rfp_proposed_date'],
            'closing_rfp_proposed_date' => $records['closing_rfp_proposed_date'],
            'updated_at' => $records['updated_at'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementRocVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_roc_verifier_versions')->insert([
            'consultant_management_recommendation_of_consultant_verifier_id' => $records['consultant_management_recommendation_of_consultant_verifier_id'],
            'user_id' => $records['user_id'],
            'version' => $records['version'],
            'status' => $records['status'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSectionDDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_section_d_details')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_approval_document_section_d_id' => $records['consultant_management_approval_document_section_d_id'],
            'company_id' => $records['company_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'scope_of_services' => $records['scope_of_services'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSectionDServiceFees()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_section_d_service_fees')->insert([
            'consultant_management_approval_document_section_d_id' => $records['consultant_management_approval_document_section_d_id'],
            'consultant_management_subsidiary_id' => $records['consultant_management_subsidiary_id'],
            'company_id' => $records['company_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'board_scale_of_fee' => $records['board_scale_of_fee'],
            'notes' => $records['notes'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_group_categories')->insert([
            'vendor_type' => $records['vendor_type'],
            'type' => $records['type'],
            'editable' => $records['editable'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'default_buildspace_access' => $records['default_buildspace_access'],
            'hidden' => $records['hidden'],
            'name' => $records['name'],
            'code' => $records['code'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementVendorCategoriesRfp()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_vendor_categories_rfp')->insert([
            'vendor_category_id' => $records['vendor_category_id'],
            'consultant_management_contract_id' => $records['consultant_management_contract_id'],
            'cost_type' => $records['cost_type'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupCategoryPrivileges()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_group_category_privileges')->insert([
            'contract_group_category_id' => $records['contract_group_category_id'],
            'identifier' => $records['identifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_groups')->insert([
            'group' => $records['group'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSubsidiaries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_subsidiaries')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_contract_id' => $records['consultant_management_contract_id'],
            'subsidiary_id' => $records['subsidiary_id'],
            'development_type_id' => $records['development_type_id'],
            'gross_acreage' => $records['gross_acreage'],
            'project_budget' => $records['project_budget'],
            'total_construction_cost' => $records['total_construction_cost'],
            'total_landscape_cost' => $records['total_landscape_cost'],
            'cost_per_square_feet' => $records['cost_per_square_feet'],
            'planning_permission_date' => $records['planning_permission_date'],
            'building_plan_date' => $records['building_plan_date'],
            'launch_date' => $records['launch_date'],
            'position' => $records['position'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'business_case' => $records['business_case'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSectionAppendixDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_section_appendix_details')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_approval_document_section_appendix_id' => $records['consultant_management_approval_document_section_appendix_id'],
            'created_at' => $records['created_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'title' => $records['title'],
            'attachment_filename' => $records['attachment_filename'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementSectionCDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_section_c_details')->insert([
            'consultant_management_approval_document_section_c_id' => $records['consultant_management_approval_document_section_c_id'],
            'consultant_management_subsidiary_id' => $records['consultant_management_subsidiary_id'],
            'company_id' => $records['company_id'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementUserRoles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_user_roles')->insert([
            'role' => $records['role'],
            'consultant_management_contract_id' => $records['consultant_management_contract_id'],
            'user_id' => $records['user_id'],
            'editor' => $records['editor'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementVendorCategoriesRfpAccountCode()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_vendor_categories_rfp_account_code')->insert([
            'vendor_category_rfp_id' => $records['vendor_category_rfp_id'],
            'account_code_id' => $records['account_code_id'],
            'amount' => $records['amount'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupContractGroupCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_group_contract_group_category')->insert([
            'contract_group_id' => $records['contract_group_id'],
            'contract_group_category_id' => $records['contract_group_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupConversation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_group_conversation')->insert([
            'contract_group_id' => $records['contract_group_id'],
            'conversation_id' => $records['conversation_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'read' => $records['read'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupDocumentManagementFolder()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_group_document_management_folder')->insert([
            'contract_group_id' => $records['contract_group_id'],
            'document_management_folder_id' => $records['document_management_folder_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupProjectUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_group_project_users')->insert([
            'contract_group_id' => $records['contract_group_id'],
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'is_contract_group_project_owner' => $records['is_contract_group_project_owner'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractGroupTenderDocumentPermissionLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_group_tender_document_permission_logs')->insert([
            'assign_company_log_id' => $records['assign_company_log_id'],
            'contract_group_id' => $records['contract_group_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractManagementUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_management_user_permissions')->insert([
            'module_identifier' => $records['module_identifier'],
            'user_id' => $records['user_id'],
            'project_id' => $records['project_id'],
            'is_editor' => $records['is_editor'],
            'is_verifier' => $records['is_verifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractLimits()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contract_limits')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'limit' => $records['limit'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaireReplies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractor_questionnaire_replies')->insert([
            'updated_at' => $records['updated_at'],
            'contractor_questionnaire_question_id' => $records['contractor_questionnaire_question_id'],
            'contractor_questionnaire_option_id' => $records['contractor_questionnaire_option_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'text' => $records['text'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaireReplyAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractor_questionnaire_reply_attachments')->insert([
            'contractor_questionnaire_question_id' => $records['contractor_questionnaire_question_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorWorkSubcategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractor_work_subcategory')->insert([
            'contractor_id' => $records['contractor_id'],
            'work_subcategory_id' => $records['work_subcategory_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorRegistrationStatuses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractor_registration_statuses')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractor_questionnaires')->insert([
            'project_id' => $records['project_id'],
            'company_id' => $records['company_id'],
            'status' => $records['status'],
            'published_date' => $records['published_date'],
            'unpublished_date' => $records['unpublished_date'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractors')->insert([
            'updated_at' => $records['updated_at'],
            'company_id' => $records['company_id'],
            'previous_cpe_grade_id' => $records['previous_cpe_grade_id'],
            'current_cpe_grade_id' => $records['current_cpe_grade_id'],
            'registration_status_id' => $records['registration_status_id'],
            'job_limit_sign' => $records['job_limit_sign'],
            'job_limit_number' => $records['job_limit_number'],
            'created_at' => $records['created_at'],
            'registered_date' => $records['registered_date'],
            'cidb_category' => $records['cidb_category'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function costData()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('cost_data')->insert([
            'buildspace_origin_id' => $records['buildspace_origin_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'notes' => $records['notes'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectStatuses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_statuses')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'status_text' => $records['status_text'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contracts()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contracts')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'type' => $records['type'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function conversations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('conversations')->insert([
            'updated_at' => $records['updated_at'],
            'project_id' => $records['project_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'purpose_of_issued' => $records['purpose_of_issued'],
            'deadline_to_reply' => $records['deadline_to_reply'],
            'status' => $records['status'],
            'send_by_contract_group_id' => $records['send_by_contract_group_id'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorsCommitmentStatusLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractors_commitment_status_logs')->insert([
            'user_id' => $records['user_id'],
            'loggable_id' => $records['loggable_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'status' => $records['status'],
            'loggable_type' => $records['loggable_type'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function conversationReplyMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('conversation_reply_messages')->insert([
            'updated_at' => $records['updated_at'],
            'conversation_id' => $records['conversation_id'],
            'created_by' => $records['created_by'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaireQuestions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractor_questionnaire_questions')->insert([
            'updated_at' => $records['updated_at'],
            'contractor_questionnaire_id' => $records['contractor_questionnaire_id'],
            'created_at' => $records['created_at'],
            'required' => $records['required'],
            'with_attachment' => $records['with_attachment'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'question' => $records['question'],
            'type' => $records['type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorQuestionnaireOptions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractor_questionnaire_options')->insert([
            'updated_at' => $records['updated_at'],
            'contractor_questionnaire_question_id' => $records['contractor_questionnaire_question_id'],
            'created_at' => $records['created_at'],
            'order' => $records['order'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'text' => $records['text'],
            'value' => $records['value'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dailyReport()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('daily_report')->insert([
            'project_id' => $records['project_id'],
            'instruction_date' => $records['instruction_date'],
            'submitted_by' => $records['submitted_by'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'instruction' => $records['instruction'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dashboardGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('dashboard_groups')->insert([
            'type' => $records['type'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'title' => $records['title'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function directedTo()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('directed_to')->insert([
            'target_id' => $records['target_id'],
            'object_id' => $records['object_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'object_type' => $records['object_type'],
            'target_type' => $records['target_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dynamicForms()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('dynamic_forms')->insert([
            'renewal_approval_required' => $records['renewal_approval_required'],
            'root_id' => $records['root_id'],
            'module_identifier' => $records['module_identifier'],
            'is_template' => $records['is_template'],
            'revision' => $records['revision'],
            'status' => $records['status'],
            'submitted_for_approval_by' => $records['submitted_for_approval_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'submission_status' => $records['submission_status'],
            'origin_id' => $records['origin_id'],
            'is_renewal_form' => $records['is_renewal_form'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingEmailReminders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('e_bidding_email_reminders')->insert([
            'ebidding_id' => $records['ebidding_id'],
            'status_bidding_start_time' => $records['status_bidding_start_time'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'status_preview_start_time' => $records['status_preview_start_time'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            'message2' => $records['message2'],
            'subject2' => $records['subject2'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function documentManagementFolders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('document_management_folders')->insert([
            'updated_at' => $records['updated_at'],
            'root_id' => $records['root_id'],
            'parent_id' => $records['parent_id'],
            'lft' => $records['lft'],
            'rgt' => $records['rgt'],
            'depth' => $records['depth'],
            'priority' => $records['priority'],
            'project_id' => $records['project_id'],
            'contract_group_id' => $records['contract_group_id'],
            'folder_type' => $records['folder_type'],
            'created_at' => $records['created_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function currentCpeGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('current_cpe_grades')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'grade' => $records['grade'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function currencySettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('currency_settings')->insert([
            'country_id' => $records['country_id'],
            'rounding_type' => $records['rounding_type'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dailyLabourReports()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('daily_labour_reports')->insert([
            'updated_at' => $records['updated_at'],
            'date' => $records['date'],
            'weather_id' => $records['weather_id'],
            'bill_column_setting_id' => $records['bill_column_setting_id'],
            'unit' => $records['unit'],
            'project_structure_location_code_id' => $records['project_structure_location_code_id'],
            'pre_defined_location_code_id' => $records['pre_defined_location_code_id'],
            'contractor_id' => $records['contractor_id'],
            'project_id' => $records['project_id'],
            'created_at' => $records['created_at'],
            'submitted_by' => $records['submitted_by'],
            'work_description' => $records['work_description'],
            'remark' => $records['remark'],
            'path_to_photo' => $records['path_to_photo'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dashboardGroupsExcludedProjects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('dashboard_groups_excluded_projects')->insert([
            'project_id' => $records['project_id'],
            'dashboard_group_type' => $records['dashboard_group_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dashboardGroupsUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('dashboard_groups_users')->insert([
            'user_id' => $records['user_id'],
            'dashboard_group_type' => $records['dashboard_group_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function defectCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('defect_categories')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function defectCategoryPreDefinedLocationCode()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('defect_category_pre_defined_location_code')->insert([
            'pre_defined_location_code_id' => $records['pre_defined_location_code_id'],
            'defect_category_id' => $records['defect_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function defects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('defects')->insert([
            'defect_category_id' => $records['defect_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function developmentTypesProductTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('development_types_product_types')->insert([
            'development_type_id' => $records['development_type_id'],
            'product_type_id' => $records['product_type_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function documentControlObjects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('document_control_objects')->insert([
            'updated_at' => $records['updated_at'],
            'project_id' => $records['project_id'],
            'reference_number' => $records['reference_number'],
            'created_at' => $records['created_at'],
            'issuer_id' => $records['issuer_id'],
            'subject' => $records['subject'],
            'message_type' => $records['message_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingCommittees()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('e_bidding_committees')->insert([
            'contract_group_id' => $records['contract_group_id'],
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'is_committee' => $records['is_committee'],
            'is_verifier' => $records['is_verifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elementAttributes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('element_attributes')->insert([
            'element_id' => $records['element_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            'element_class' => $records['element_class'],
            'value' => $records['value'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailAnnouncementRecipients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('email_announcement_recipients')->insert([
            'email_announcement_id' => $records['email_announcement_id'],
            'contract_group_category_id' => $records['contract_group_category_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function engineerInstructions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('engineer_instructions')->insert([
            'type' => $records['type'],
            'project_id' => $records['project_id'],
            'created_by' => $records['created_by'],
            'updated_at' => $records['updated_at'],
            'deadline_to_comply_with' => $records['deadline_to_comply_with'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'subject' => $records['subject'],
            'detailed_elaborations' => $records['detailed_elaborations'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('elements')->insert([
            'has_attachments' => $records['has_attachments'],
            'parent_id' => $records['parent_id'],
            'updated_at' => $records['updated_at'],
            'is_other_option' => $records['is_other_option'],
            'is_key_information' => $records['is_key_information'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'label' => $records['label'],
            'instructions' => $records['instructions'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailNotifications()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('email_notifications')->insert([
            'updated_at' => $records['updated_at'],
            'project_id' => $records['project_id'],
            'created_at' => $records['created_at'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailNotificationSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('email_notification_settings')->insert([
            'setting_identifier' => $records['setting_identifier'],
            'activated' => $records['activated'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'modifiable_contents' => $records['modifiable_contents'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elementDefinitions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('element_definitions')->insert([
            'element_render_identifier' => $records['element_render_identifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'module_class' => $records['module_class'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailReminderSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('email_reminder_settings')->insert([
            'tender_reminder_before_closing_date_value' => $records['tender_reminder_before_closing_date_value'],
            'tender_reminder_before_closing_date_unit' => $records['tender_reminder_before_closing_date_unit'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('email_settings')->insert([
            'company_logo_alignment_identifier' => $records['company_logo_alignment_identifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'footer_logo_height' => $records['footer_logo_height'],
            'resize_footer_image' => $records['resize_footer_image'],
            'footer_logo_width' => $records['footer_logo_width'],
            'footer_logo_image' => $records['footer_logo_image'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elementValues()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('element_values')->insert([
            'element_id' => $records['element_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'element_class' => $records['element_class'],
            'value' => $records['value'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function elementRejections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('element_rejections')->insert([
            'updated_by' => $records['updated_by'],
            'element_id' => $records['element_id'],
            'created_by' => $records['created_by'],
            'is_amended' => $records['is_amended'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'element_class' => $records['element_class'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailAnnouncements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('email_announcements')->insert([
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('e_biddings')->insert([
            'project_id' => $records['project_id'],
            'preview_start_time' => $records['preview_start_time'],
            'reminder_preview_start_time' => $records['reminder_preview_start_time'],
            'bidding_start_time' => $records['bidding_start_time'],
            'reminder_bidding_start_time' => $records['reminder_bidding_start_time'],
            'duration_hours' => $records['duration_hours'],
            'duration_minutes' => $records['duration_minutes'],
            'start_overtime' => $records['start_overtime'],
            'overtime_period' => $records['overtime_period'],
            'set_budget' => $records['set_budget'],
            'budget' => $records['budget'],
            'bid_decrement_percent' => $records['bid_decrement_percent'],
            'decrement_percent' => $records['decrement_percent'],
            'bid_decrement_value' => $records['bid_decrement_value'],
            'decrement_value' => $records['decrement_value'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'duration_extended' => $records['duration_extended'],
            'lowest_tender_amount' => $records['lowest_tender_amount'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function extensionOfTimes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('extension_of_times')->insert([
            'project_id' => $records['project_id'],
            'architect_instruction_id' => $records['architect_instruction_id'],
            'created_by' => $records['created_by'],
            'commencement_date_of_event' => $records['commencement_date_of_event'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'initial_estimate_of_eot' => $records['initial_estimate_of_eot'],
            'days_claimed' => $records['days_claimed'],
            'days_granted' => $records['days_granted'],
            'status' => $records['status'],
            'subject' => $records['subject'],
            'detailed_elaborations' => $records['detailed_elaborations'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotFourthLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('eot_fourth_level_messages')->insert([
            'updated_at' => $records['updated_at'],
            'extension_of_time_id' => $records['extension_of_time_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'grant_different_days' => $records['grant_different_days'],
            'decision' => $records['decision'],
            'type' => $records['type'],
            'locked' => $records['locked'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotSecondLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('eot_second_level_messages')->insert([
            'updated_at' => $records['updated_at'],
            'extension_of_time_id' => $records['extension_of_time_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'requested_new_deadline' => $records['requested_new_deadline'],
            'grant_different_deadline' => $records['grant_different_deadline'],
            'decision' => $records['decision'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotThirdLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('eot_third_level_messages')->insert([
            'extension_of_time_id' => $records['extension_of_time_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deadline_to_comply_with' => $records['deadline_to_comply_with'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationAttributes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('external_application_attributes')->insert([
            'updated_at' => $records['updated_at'],
            'client_module_id' => $records['client_module_id'],
            'internal_attribute' => $records['internal_attribute'],
            'is_identifier' => $records['is_identifier'],
            'created_at' => $records['created_at'],
            'external_attribute' => $records['external_attribute'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function expressionOfInterestTokens()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('expression_of_interest_tokens')->insert([
            'updated_at' => $records['updated_at'],
            'tenderstageable_id' => $records['tenderstageable_id'],
            'created_at' => $records['created_at'],
            'user_id' => $records['user_id'],
            'company_id' => $records['company_id'],
            'tenderstageable_type' => $records['tenderstageable_type'],
            'token' => $records['token'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotFirstLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('eot_first_level_messages')->insert([
            'extension_of_time_id' => $records['extension_of_time_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'decision' => $records['decision'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'details' => $records['details'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalAppAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('external_app_attachments')->insert([
            'reference_id' => $records['reference_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'filename' => $records['filename'],
            'remarks' => $records['remarks'],
            'file_path' => $records['file_path'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalAppCompanyAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('external_app_company_attachments')->insert([
            'reference_id' => $records['reference_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'filename' => $records['filename'],
            'document_type' => $records['document_type'],
            'file_path' => $records['file_path'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eotContractorConfirmDelays()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('eot_contractor_confirm_delays')->insert([
            'extension_of_time_id' => $records['extension_of_time_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'date_on_which_delay_is_over' => $records['date_on_which_delay_is_over'],
            'deadline_to_submit_final_eot_claim' => $records['deadline_to_submit_final_eot_claim'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function extensionOfTimeClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('extension_of_time_claims')->insert([
            'extension_of_time_id' => $records['extension_of_time_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'days_claimed' => $records['days_claimed'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationClientOutboundLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('external_application_client_outbound_logs')->insert([
            'client_id' => $records['client_id'],
            'data' => $records['data'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'module' => $records['module'],
            'response_contents' => $records['response_contents'],
            'status_code' => $records['status_code'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationIdentifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('external_application_identifiers')->insert([
            'client_module_id' => $records['client_module_id'],
            'internal_identifier' => $records['internal_identifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'class_name' => $records['class_name'],
            'external_identifier' => $records['external_identifier'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationClientOutboundAuthorizations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('external_application_client_outbound_authorizations')->insert([
            'client_id' => $records['client_id'],
            'type' => $records['type'],
            'options' => $records['options'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'url' => $records['url'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function fileNodePermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('file_node_permissions')->insert([
            'user_id' => $records['user_id'],
            'file_node_id' => $records['file_node_id'],
            'is_editor' => $records['is_editor'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function failedJobs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('failed_jobs')->insert([
            'failed_at' => $records['failed_at'],
            'connection' => $records['connection'],
            'queue' => $records['queue'],
            'payload' => $records['payload'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function financeUserSubsidiaries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('finance_user_subsidiaries')->insert([
            'user_id' => $records['user_id'],
            'subsidiary_id' => $records['subsidiary_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_of_tender_clauses')->insert([
            'form_of_tender_id' => $records['form_of_tender_id'],
            'parent_id' => $records['parent_id'],
            'sequence_number' => $records['sequence_number'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'is_editable' => $records['is_editable'],
            'clause' => $records['clause'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationClients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('external_application_clients')->insert([
            'updated_at' => $records['updated_at'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'user_id' => $records['user_id'],
            'created_by' => $records['created_by'],
            'name' => $records['name'],
            'token' => $records['token'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function fileNodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('file_nodes')->insert([
            'deleted_at' => $records['deleted_at'],
            'parent_id' => $records['parent_id'],
            'lft' => $records['lft'],
            'rgt' => $records['rgt'],
            'depth' => $records['depth'],
            'root_id' => $records['root_id'],
            'priority' => $records['priority'],
            'type' => $records['type'],
            'version' => $records['version'],
            'is_latest_version' => $records['is_latest_version'],
            'origin_id' => $records['origin_id'],
            'upload_id' => $records['upload_id'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'name' => $records['name'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formColumns()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_columns')->insert([
            'dynamic_form_id' => $records['dynamic_form_id'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formElementMappings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_element_mappings')->insert([
            'updated_at' => $records['updated_at'],
            'form_column_section_id' => $records['form_column_section_id'],
            'element_id' => $records['element_id'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'element_class' => $records['element_class'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formObjectMappings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_object_mappings')->insert([
            'updated_by' => $records['updated_by'],
            'object_id' => $records['object_id'],
            'dynamic_form_id' => $records['dynamic_form_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'object_class' => $records['object_class'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_of_tenders')->insert([
            'tender_id' => $records['tender_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'is_template' => $records['is_template'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderAddresses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_of_tender_addresses')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'form_of_tender_id' => $records['form_of_tender_id'],
            'address' => $records['address'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_of_tender_logs')->insert([
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'form_of_tender_id' => $records['form_of_tender_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderTenderAlternatives()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_of_tender_tender_alternatives')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'form_of_tender_id' => $records['form_of_tender_id'],
            'show' => $records['show'],
            'tender_alternative_class_name' => $records['tender_alternative_class_name'],
            'custom_description' => $records['custom_description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumPosts()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('forum_posts')->insert([
            'thread_id' => $records['thread_id'],
            'parent_id' => $records['parent_id'],
            'original_post_id' => $records['original_post_id'],
            'deleted_at' => $records['deleted_at'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'content' => $records['content'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumPostsReadLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('forum_posts_read_log')->insert([
            'user_id' => $records['user_id'],
            'post_id' => $records['post_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function generalSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('general_settings')->insert([
            'view_own_created_subsidiary' => $records['view_own_created_subsidiary'],
            'view_tenders' => $records['view_tenders'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'enable_e_bidding' => $records['enable_e_bidding'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractContractualClaimResponses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('indonesia_civil_contract_contractual_claim_responses')->insert([
            'user_id' => $records['user_id'],
            'proposed_value' => $records['proposed_value'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'object_id' => $records['object_id'],
            'sequence' => $records['sequence'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'content' => $records['content'],
            'object_type' => $records['object_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderHeaders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_of_tender_headers')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'form_of_tender_id' => $records['form_of_tender_id'],
            'header_text' => $records['header_text'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formOfTenderPrintSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_of_tender_print_settings')->insert([
            'form_of_tender_id' => $records['form_of_tender_id'],
            'margin_bottom' => $records['margin_bottom'],
            'margin_left' => $records['margin_left'],
            'margin_right' => $records['margin_right'],
            'include_header_line' => $records['include_header_line'],
            'header_spacing' => $records['header_spacing'],
            'margin_top' => $records['margin_top'],
            'footer_font_size' => $records['footer_font_size'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'font_size' => $records['font_size'],
            'title_text' => $records['title_text'],
            'footer_text' => $records['footer_text'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumThreads()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('forum_threads')->insert([
            'type' => $records['type'],
            'project_id' => $records['project_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            'title' => $records['title'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumThreadPrivacyLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('forum_thread_privacy_log')->insert([
            'thread_id' => $records['thread_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'type' => $records['type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumThreadUser()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('forum_thread_user')->insert([
            'thread_id' => $records['thread_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function icInfoGrossValuesAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('ic_info_gross_values_attachments')->insert([
            'interim_claim_information_id' => $records['interim_claim_information_id'],
            'upload_id' => $records['upload_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function icInfoNettAdditionOmissionAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('ic_info_nett_addition_omission_attachments')->insert([
            'interim_claim_information_id' => $records['interim_claim_information_id'],
            'upload_id' => $records['upload_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractArchitectInstructions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('indonesia_civil_contract_architect_instructions')->insert([
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deadline_to_comply' => $records['deadline_to_comply'],
            'status' => $records['status'],
            'reference' => $records['reference'],
            'instruction' => $records['instruction'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractAiRfi()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('indonesia_civil_contract_ai_rfi')->insert([
            'indonesia_civil_contract_architect_instruction_id' => $records['indonesia_civil_contract_architect_instruction_id'],
            'document_control_object_id' => $records['document_control_object_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingRankings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('e_bidding_rankings')->insert([
            'e_bidding_id' => $records['e_bidding_id'],
            'company_id' => $records['company_id'],
            'bid_amount' => $records['bid_amount'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractEwLe()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('indonesia_civil_contract_ew_le')->insert([
            'indonesia_civil_contract_ew_id' => $records['indonesia_civil_contract_ew_id'],
            'indonesia_civil_contract_le_id' => $records['indonesia_civil_contract_le_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('indonesia_civil_contract_information')->insert([
            'project_id' => $records['project_id'],
            'commencement_date' => $records['commencement_date'],
            'completion_date' => $records['completion_date'],
            'contract_sum' => $records['contract_sum'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'pre_defined_location_code_id' => $records['pre_defined_location_code_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionResults()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_results')->insert([
            'inspection_id' => $records['inspection_id'],
            'inspection_role_id' => $records['inspection_role_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'submitted_by' => $records['submitted_by'],
            'submitted_at' => $records['submitted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionSubmitters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_submitters')->insert([
            'inspection_group_id' => $records['inspection_group_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionLists()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_lists')->insert([
            'project_id' => $records['project_id'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractExtensionsOfTime()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('indonesia_civil_contract_extensions_of_time')->insert([
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'indonesia_civil_contract_ai_id' => $records['indonesia_civil_contract_ai_id'],
            'days' => $records['days'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'status' => $records['status'],
            'reference' => $records['reference'],
            'subject' => $records['subject'],
            'details' => $records['details'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractLossAndExpenses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('indonesia_civil_contract_loss_and_expenses')->insert([
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'indonesia_civil_contract_ai_id' => $records['indonesia_civil_contract_ai_id'],
            'claim_amount' => $records['claim_amount'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'status' => $records['status'],
            'reference' => $records['reference'],
            'subject' => $records['subject'],
            'details' => $records['details'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_groups')->insert([
            'project_id' => $records['project_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionGroupInspectionListCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_group_inspection_list_category')->insert([
            'inspection_group_id' => $records['inspection_group_id'],
            'inspection_list_category_id' => $records['inspection_list_category_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionListCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_list_categories')->insert([
            'updated_at' => $records['updated_at'],
            'inspection_list_id' => $records['inspection_list_id'],
            'parent_id' => $records['parent_id'],
            'lft' => $records['lft'],
            'rgt' => $records['rgt'],
            'depth' => $records['depth'],
            'type' => $records['type'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionGroupUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_group_users')->insert([
            'inspection_role_id' => $records['inspection_role_id'],
            'inspection_group_id' => $records['inspection_group_id'],
            'user_id' => $records['user_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionRoles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_roles')->insert([
            'project_id' => $records['project_id'],
            'can_request_inspection' => $records['can_request_inspection'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionListItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_list_items')->insert([
            'updated_at' => $records['updated_at'],
            'inspection_list_category_id' => $records['inspection_list_category_id'],
            'parent_id' => $records['parent_id'],
            'lft' => $records['lft'],
            'rgt' => $records['rgt'],
            'depth' => $records['depth'],
            'priority' => $records['priority'],
            'type' => $records['type'],
            'created_at' => $records['created_at'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionItemResults()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_item_results')->insert([
            'progress_status' => $records['progress_status'],
            'inspection_result_id' => $records['inspection_result_id'],
            'inspection_list_item_id' => $records['inspection_list_item_id'],
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionListCategoryAdditionalFields()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_list_category_additional_fields')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'inspection_list_category_id' => $records['inspection_list_category_id'],
            'priority' => $records['priority'],
            'value' => $records['value'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspectionVerifierTemplate()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspection_verifier_template')->insert([
            'inspection_group_id' => $records['inspection_group_id'],
            'user_id' => $records['user_id'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardClauseComments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('letter_of_award_clause_comments')->insert([
            'clause_id' => $records['clause_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'comments' => $records['comments'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function inspections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('inspections')->insert([
            'updated_at' => $records['updated_at'],
            'request_for_inspection_id' => $records['request_for_inspection_id'],
            'revision' => $records['revision'],
            'ready_for_inspection_date' => $records['ready_for_inspection_date'],
            'status' => $records['status'],
            'decision' => $records['decision'],
            'created_at' => $records['created_at'],
            'comments' => $records['comments'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function interimClaimInformations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('interim_claim_informations')->insert([
            'updated_at' => $records['updated_at'],
            'interim_claim_id' => $records['interim_claim_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'date' => $records['date'],
            'nett_addition_omission' => $records['nett_addition_omission'],
            'date_of_certificate' => $records['date_of_certificate'],
            'net_amount_of_payment_certified' => $records['net_amount_of_payment_certified'],
            'gross_values_of_works' => $records['gross_values_of_works'],
            'type' => $records['type'],
            'reference' => $records['reference'],
            'amount_in_word' => $records['amount_in_word'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('letter_of_award_clauses')->insert([
            'updated_at' => $records['updated_at'],
            'letter_of_award_id' => $records['letter_of_award_id'],
            'display_numbering' => $records['display_numbering'],
            'sequence_number' => $records['sequence_number'],
            'parent_id' => $records['parent_id'],
            'created_at' => $records['created_at'],
            'contents' => $records['contents'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardContractDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('letter_of_award_contract_details')->insert([
            'letter_of_award_id' => $records['letter_of_award_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'contents' => $records['contents'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('letter_of_award_logs')->insert([
            'letter_of_award_id' => $records['letter_of_award_id'],
            'type_identifier' => $records['type_identifier'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function labours()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('labours')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function instructionsToContractors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('instructions_to_contractors')->insert([
            'project_id' => $records['project_id'],
            'instruction_date' => $records['instruction_date'],
            'submitted_by' => $records['submitted_by'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'instruction' => $records['instruction'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardPrintSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('letter_of_award_print_settings')->insert([
            'letter_of_award_id' => $records['letter_of_award_id'],
            'header_font_size' => $records['header_font_size'],
            'clause_font_size' => $records['clause_font_size'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'margin_top' => $records['margin_top'],
            'margin_bottom' => $records['margin_bottom'],
            'margin_left' => $records['margin_left'],
            'margin_right' => $records['margin_right'],
            'header_spacing' => $records['header_spacing'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardSignatories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('letter_of_award_signatories')->insert([
            'letter_of_award_id' => $records['letter_of_award_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'contents' => $records['contents'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function languages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('languages')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            'code' => $records['code'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function licenses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('licenses')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'license_key' => $records['license_key'],
            'decryption_key' => $records['decryption_key'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function lossOrAndExpenses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('loss_or_and_expenses')->insert([
            'project_id' => $records['project_id'],
            'architect_instruction_id' => $records['architect_instruction_id'],
            'created_by' => $records['created_by'],
            'commencement_date_of_event' => $records['commencement_date_of_event'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'initial_estimate_of_claim' => $records['initial_estimate_of_claim'],
            'amount_claimed' => $records['amount_claimed'],
            'amount_granted' => $records['amount_granted'],
            'status' => $records['status'],
            'subject' => $records['subject'],
            'detailed_elaborations' => $records['detailed_elaborations'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeFourthLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('loe_fourth_level_messages')->insert([
            'updated_at' => $records['updated_at'],
            'loss_or_and_expense_id' => $records['loss_or_and_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'grant_different_amount' => $records['grant_different_amount'],
            'decision' => $records['decision'],
            'type' => $records['type'],
            'locked' => $records['locked'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeSecondLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('loe_second_level_messages')->insert([
            'updated_at' => $records['updated_at'],
            'loss_or_and_expense_id' => $records['loss_or_and_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'requested_new_deadline' => $records['requested_new_deadline'],
            'grant_different_deadline' => $records['grant_different_deadline'],
            'decision' => $records['decision'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeThirdLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('loe_third_level_messages')->insert([
            'loss_or_and_expense_id' => $records['loss_or_and_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deadline_to_comply_with' => $records['deadline_to_comply_with'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function lossOrAndExpenseClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('loss_or_and_expense_claims')->insert([
            'loss_or_and_expense_id' => $records['loss_or_and_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'final_claim_amount' => $records['final_claim_amount'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loginRequestFormSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('login_request_form_settings')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'include_instructions' => $records['include_instructions'],
            'include_disclaimer' => $records['include_disclaimer'],
            'disclaimer' => $records['disclaimer'],
            'instructions' => $records['instructions'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function lossOrAndExpenseInterimClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('loss_or_and_expense_interim_claims')->insert([
            'loss_or_and_expense_id' => $records['loss_or_and_expense_id'],
            'interim_claim_id' => $records['interim_claim_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeFirstLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('loe_first_level_messages')->insert([
            'loss_or_and_expense_id' => $records['loss_or_and_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'decision' => $records['decision'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'details' => $records['details'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwards()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('letter_of_awards')->insert([
            'project_id' => $records['project_id'],
            'is_template' => $records['is_template'],
            'status' => $records['status'],
            'submitted_for_approval_by' => $records['submitted_for_approval_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('letter_of_award_user_permissions')->insert([
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'module_identifier' => $records['module_identifier'],
            'is_editor' => $records['is_editor'],
            'added_by' => $records['added_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function loeContractorConfirmDelays()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('loe_contractor_confirm_delays')->insert([
            'loss_or_and_expense_id' => $records['loss_or_and_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'date_on_which_delay_is_over' => $records['date_on_which_delay_is_over'],
            'deadline_to_submit_final_claim' => $records['deadline_to_submit_final_claim'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function machinery()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('machinery')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingBids()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('e_bidding_bids')->insert([
            'updated_at' => $records['updated_at'],
            'e_bidding_id' => $records['e_bidding_id'],
            'company_id' => $records['company_id'],
            'duration_extended' => $records['duration_extended'],
            'decrement_percent' => $records['decrement_percent'],
            'decrement_value' => $records['decrement_value'],
            'decrement_amount' => $records['decrement_amount'],
            'bid_amount' => $records['bid_amount'],
            'created_at' => $records['created_at'],
            'bid_type' => $records['bid_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function migrations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('migrations')->insert([
            'batch' => $records['batch'],
            'migration' => $records['migration'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function myCompanyProfiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('my_company_profiles')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            'company_logo_path' => $records['company_logo_path'],
            'company_logo_filename' => $records['company_logo_filename'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function notificationGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('notification_groups')->insert([
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectForumThreads()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('object_forum_threads')->insert([
            'thread_id' => $records['thread_id'],
            'object_id' => $records['object_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'object_type' => $records['object_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectFields()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('object_fields')->insert([
            'object_id' => $records['object_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'object_type' => $records['object_type'],
            'field' => $records['field'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncCompanies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_companies')->insert([
            'company_id' => $records['company_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncDefectCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_defect_categories')->insert([
            'defect_category_id' => $records['defect_category_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncDefectCategoryTrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_defect_category_trades')->insert([
            'defect_category_trade_id' => $records['defect_category_trade_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncDefects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_defects')->insert([
            'defect_id' => $records['defect_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjectLabourRateContractors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_project_labour_rate_contractors')->insert([
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'project_labour_rate_contractor_id' => $records['project_labour_rate_contractor_id'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjectLabourRateTrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_project_labour_rate_trades')->insert([
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'project_labour_rate_trade_id' => $records['project_labour_rate_trade_id'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjectLabourRates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_project_labour_rates')->insert([
            'project_labour_rate_id' => $records['project_labour_rate_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjectStructureLocationCodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_project_structure_location_codes')->insert([
            'project_structure_location_code_id' => $records['project_structure_location_code_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncProjects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_projects')->insert([
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncSiteManagementDefects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_site_management_defects')->insert([
            'site_management_defect_id' => $records['site_management_defect_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncTrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_trades')->insert([
            'trade_id' => $records['trade_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function mobileSyncUploads()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('mobile_sync_uploads')->insert([
            'upload_id' => $records['upload_id'],
            'user_id' => $records['user_id'],
            'synced' => $records['synced'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'device_id' => $records['device_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function modulePermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('module_permissions')->insert([
            'user_id' => $records['user_id'],
            'module_identifier' => $records['module_identifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'is_editor' => $records['is_editor'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function moduleUploadedFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('module_uploaded_files')->insert([
            'type' => $records['type'],
            'upload_id' => $records['upload_id'],
            'uploadable_id' => $records['uploadable_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'uploadable_type' => $records['uploadable_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function notificationCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('notification_categories')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            'text' => $records['text'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function notifications()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('notifications')->insert([
            'updated_at' => $records['updated_at'],
            'from_id' => $records['from_id'],
            'category_id' => $records['category_id'],
            'read' => $records['read'],
            'created_at' => $records['created_at'],
            'to_id' => $records['to_id'],
            'from_type' => $records['from_type'],
            'url' => $records['url'],
            'to_type' => $records['to_type'],
            'extra' => $records['extra'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('object_logs')->insert([
            'updated_at' => $records['updated_at'],
            'object_id' => $records['object_id'],
            'module_identifier' => $records['module_identifier'],
            'action_identifier' => $records['action_identifier'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'object_class' => $records['object_class'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderBanners()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_banners')->insert([
            'updated_at' => $records['updated_at'],
            'display_order' => $records['display_order'],
            'start_time' => $records['start_time'],
            'end_time' => $records['end_time'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'image' => $records['image'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationTenderAnalysisTableEditLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_award_recommendation_tender_analysis_table_edit_log')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'tender_id' => $records['tender_id'],
            'user_id' => $records['user_id'],
            'type' => $records['type'],
            'table_name' => $records['table_name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function eBiddingEmailReminderRecipients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('e_bidding_email_reminder_recipients')->insert([
            'email_reminder_id' => $records['email_reminder_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'role' => $records['role'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationTenderSummary()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_award_recommendation_tender_summary')->insert([
            'tender_id' => $records['tender_id'],
            'consultant_estimate' => $records['consultant_estimate'],
            'budget' => $records['budget'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderPageInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_page_information')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'open_tender_status' => $records['open_tender_status'],
            'submitted_for_approval_by' => $records['submitted_for_approval_by'],
            'status' => $records['status'],
            'project_id' => $records['project_id'],
            'open_tender_date_to' => $records['open_tender_date_to'],
            'created_by' => $records['created_by'],
            'tender_id' => $records['tender_id'],
            'open_tender_date_from' => $records['open_tender_date_from'],
            'special_permission' => $records['special_permission'],
            'local_company_only' => $records['local_company_only'],
            'closing_date' => $records['closing_date'],
            'open_tender_type' => $records['open_tender_type'],
            'open_tender_number' => $records['open_tender_number'],
            'open_tender_price' => $records['open_tender_price'],
            'deliver_address' => $records['deliver_address'],
            'briefing_time' => $records['briefing_time'],
            'briefing_address' => $records['briefing_address'],
            'calling_date' => $records['calling_date'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderPersonInCharges()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_person_in_charges')->insert([
            'tender_id' => $records['tender_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'phone_number' => $records['phone_number'],
            'department' => $records['department'],
            'name' => $records['name'],
            'email' => $records['email'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderTenderDocuments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_tender_documents')->insert([
            'tender_id' => $records['tender_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationBillDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_award_recommendation_bill_details')->insert([
            'tender_id' => $records['tender_id'],
            'buildspace_bill_id' => $records['buildspace_bill_id'],
            'consultant_pte' => $records['consultant_pte'],
            'budget' => $records['budget'],
            'bill_amount' => $records['bill_amount'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectTags()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('object_tags')->insert([
            'tag_id' => $records['tag_id'],
            'object_id' => $records['object_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'object_class' => $records['object_class'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAnnouncements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_announcements')->insert([
            'date' => $records['date'],
            'tender_id' => $records['tender_id'],
            'created_by' => $records['created_by'],
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderIndustryCodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_industry_codes')->insert([
            'tender_id' => $records['tender_id'],
            'created_by' => $records['created_by'],
            'cidb_code_id' => $records['cidb_code_id'],
            'cidb_grade_id' => $records['cidb_grade_id'],
            'vendor_category_id' => $records['vendor_category_id'],
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderNews()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_news')->insert([
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'subsidiary_id' => $records['subsidiary_id'],
            'start_time' => $records['start_time'],
            'end_time' => $records['end_time'],
            'created_by' => $records['created_by'],
            'description' => $records['description'],
            'status' => $records['status'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_award_recommendation_files')->insert([
            'tender_id' => $records['tender_id'],
            'cabinet_file_id' => $records['cabinet_file_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'filename' => $records['filename'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_award_recommendation')->insert([
            'updated_at' => $records['updated_at'],
            'tender_id' => $records['tender_id'],
            'created_by' => $records['created_by'],
            'submitted_for_verification_by' => $records['submitted_for_verification_by'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'report_contents' => $records['report_contents'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderVerifierLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_verifier_logs')->insert([
            'tender_id' => $records['tender_id'],
            'user_id' => $records['user_id'],
            'type' => $records['type'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderItemProjectTenders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('order_item_project_tenders')->insert([
            'order_item_id' => $records['order_item_id'],
            'project_id' => $records['project_id'],
            'tender_id' => $records['tender_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderItemVendorRegPayments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('order_item_vendor_reg_payments')->insert([
            'order_item_id' => $records['order_item_id'],
            'vendor_registration_payment_id' => $records['vendor_registration_payment_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('order_items')->insert([
            'quantity' => $records['quantity'],
            'order_sub_id' => $records['order_sub_id'],
            'updated_at' => $records['updated_at'],
            'total' => $records['total'],
            'created_at' => $records['created_at'],
            'type' => $records['type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderPayments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('order_payments')->insert([
            'updated_at' => $records['updated_at'],
            'order_id' => $records['order_id'],
            'total' => $records['total'],
            'created_at' => $records['created_at'],
            'description' => $records['description'],
            'status' => $records['status'],
            'payment_gateway' => $records['payment_gateway'],
            'transaction_id' => $records['transaction_id'],
            'reference_id' => $records['reference_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orderSubs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('order_subs')->insert([
            'company_id' => $records['company_id'],
            'order_id' => $records['order_id'],
            'updated_at' => $records['updated_at'],
            'total' => $records['total'],
            'created_at' => $records['created_at'],
            'reference_id' => $records['reference_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function orders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('orders')->insert([
            'company_id' => $records['company_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'user_id' => $records['user_id'],
            'reference_id' => $records['reference_id'],
            'origin' => $records['origin'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function passwordReminders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('password_reminders')->insert([
            'created_at' => $records['created_at'],
            'email' => $records['email'],
            'token' => $records['token'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function paymentGatewayResults()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('payment_gateway_results')->insert([
            'verified' => $records['verified'],
            'is_ipn' => $records['is_ipn'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'info' => $records['info'],
            'data' => $records['data'],
            'payment_gateway' => $records['payment_gateway'],
            'transaction_id' => $records['transaction_id'],
            'reference_id' => $records['reference_id'],
            'status' => $records['status'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function paymentGatewaySettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('payment_gateway_settings')->insert([
            'is_sandbox' => $records['is_sandbox'],
            'is_active' => $records['is_active'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'key1' => $records['key1'],
            'payment_gateway' => $records['payment_gateway'],
            'key2' => $records['key2'],
            'button_image_url' => $records['button_image_url'],
            'merchant_id' => $records['merchant_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function processorDeleteCompanyLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('processor_delete_company_logs')->insert([
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'contract_group_category_id' => $records['contract_group_category_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'name' => $records['name'],
            'reference_no' => $records['reference_no'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function procurementMethods()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('procurement_methods')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderTenderRequirements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_tender_requirements')->insert([
            'tender_id' => $records['tender_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function pam2006ProjectDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('pam_2006_project_details')->insert([
            'project_id' => $records['project_id'],
            'commencement_date' => $records['commencement_date'],
            'completion_date' => $records['completion_date'],
            'contract_sum' => $records['contract_sum'],
            'liquidate_damages' => $records['liquidate_damages'],
            'amount_performance_bond' => $records['amount_performance_bond'],
            'interim_claim_interval' => $records['interim_claim_interval'],
            'period_of_honouring_certificate' => $records['period_of_honouring_certificate'],
            'min_days_to_comply_with_ai' => $records['min_days_to_comply_with_ai'],
            'deadline_submitting_notice_of_intention_claim_eot' => $records['deadline_submitting_notice_of_intention_claim_eot'],
            'deadline_submitting_final_claim_eot' => $records['deadline_submitting_final_claim_eot'],
            'deadline_architect_request_info_from_contractor_eot_claim' => $records['deadline_architect_request_info_from_contractor_eot_claim'],
            'deadline_architect_decide_on_contractor_eot_claim' => $records['deadline_architect_decide_on_contractor_eot_claim'],
            'deadline_submitting_note_of_intention_claim_l_and_e' => $records['deadline_submitting_note_of_intention_claim_l_and_e'],
            'deadline_submitting_final_claim_l_and_e' => $records['deadline_submitting_final_claim_l_and_e'],
            'deadline_submitting_note_of_intention_claim_ae' => $records['deadline_submitting_note_of_intention_claim_ae'],
            'deadline_submitting_final_claim_ae' => $records['deadline_submitting_final_claim_ae'],
            'percentage_of_certified_value_retained' => $records['percentage_of_certified_value_retained'],
            'limit_retention_fund' => $records['limit_retention_fund'],
            'percentage_value_of_materials_and_goods_included_in_certificate' => $records['percentage_value_of_materials_and_goods_included_in_certificate'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'period_of_architect_issue_interim_certificate' => $records['period_of_architect_issue_interim_certificate'],
            'pre_defined_location_code_id' => $records['pre_defined_location_code_id'],
            'cpc_date' => $records['cpc_date'],
            'extension_of_time_date' => $records['extension_of_time_date'],
            'defect_liability_period' => $records['defect_liability_period'],
            'defect_liability_period_unit' => $records['defect_liability_period_unit'],
            'certificate_of_making_good_defect_date' => $records['certificate_of_making_good_defect_date'],
            'cnc_date' => $records['cnc_date'],
            'performance_bond_validity_date' => $records['performance_bond_validity_date'],
            'insurance_policy_coverage_date' => $records['insurance_policy_coverage_date'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function paymentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('payment_settings')->insert([
            'is_user_selectable' => $records['is_user_selectable'],
            'updated_at' => $records['updated_at'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'name' => $records['name'],
            'account_number' => $records['account_number'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectModulePermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_module_permissions')->insert([
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'module_identifier' => $records['module_identifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportChartPlots()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_chart_plots')->insert([
            'project_report_chart_id' => $records['project_report_chart_id'],
            'category_column_id' => $records['category_column_id'],
            'value_column_id' => $records['value_column_id'],
            'plot_type' => $records['plot_type'],
            'data_grouping' => $records['data_grouping'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'is_accumulated' => $records['is_accumulated'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportCharts()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_charts')->insert([
            'project_report_type_mapping_id' => $records['project_report_type_mapping_id'],
            'chart_type' => $records['chart_type'],
            'is_locked' => $records['is_locked'],
            'order' => $records['order'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'is_published' => $records['is_published'],
            'title' => $records['title'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportTypeMappings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_type_mappings')->insert([
            'project_report_type_id' => $records['project_report_type_id'],
            'project_type' => $records['project_type'],
            'project_report_id' => $records['project_report_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'latest_rev' => $records['latest_rev'],
            'is_locked' => $records['is_locked'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportNotificationContents()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_notification_contents')->insert([
            'project_report_notification_id' => $records['project_report_notification_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'subject' => $records['subject'],
            'body' => $records['body'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportNotificationPeriods()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_notification_periods')->insert([
            'project_report_notification_id' => $records['project_report_notification_id'],
            'period_value' => $records['period_value'],
            'period_type' => $records['period_type'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportNotificationRecipients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_notification_recipients')->insert([
            'project_report_notification_id' => $records['project_report_notification_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportNotifications()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_notifications')->insert([
            'updated_at' => $records['updated_at'],
            'project_id' => $records['project_id'],
            'project_report_type_mapping_id' => $records['project_report_type_mapping_id'],
            'category_column_id' => $records['category_column_id'],
            'notification_type' => $records['notification_type'],
            'is_published' => $records['is_published'],
            'created_at' => $records['created_at'],
            'template_name' => $records['template_name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportColumns()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_columns')->insert([
            'single_entry' => $records['single_entry'],
            'project_report_id' => $records['project_report_id'],
            'reference_id' => $records['reference_id'],
            'type' => $records['type'],
            'parent_id' => $records['parent_id'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'depth' => $records['depth'],
            'title' => $records['title'],
            'content' => $records['content'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectLabourRates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_labour_rates')->insert([
            'labour_type' => $records['labour_type'],
            'normal_working_hours' => $records['normal_working_hours'],
            'normal_rate_per_hour' => $records['normal_rate_per_hour'],
            'ot_rate_per_hour' => $records['ot_rate_per_hour'],
            'project_id' => $records['project_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'submitted_by' => $records['submitted_by'],
            'pre_defined_location_code_id' => $records['pre_defined_location_code_id'],
            'contractor_id' => $records['contractor_id'],
            'mobile_sync_uuid' => $records['mobile_sync_uuid'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectContractGroupTenderDocumentPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_contract_group_tender_document_permissions')->insert([
            'project_id' => $records['project_id'],
            'contract_group_id' => $records['contract_group_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectContractManagementModules()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_contract_management_modules')->insert([
            'project_id' => $records['project_id'],
            'module_identifier' => $records['module_identifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectDocumentFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_document_files')->insert([
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'cabinet_file_id' => $records['cabinet_file_id'],
            'project_document_folder_id' => $records['project_document_folder_id'],
            'revision' => $records['revision'],
            'parent_id' => $records['parent_id'],
            'filename' => $records['filename'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportActionLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_action_logs')->insert([
            'project_report_id' => $records['project_report_id'],
            'action_type' => $records['action_type'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReports()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_reports')->insert([
            'project_id' => $records['project_id'],
            'root_id' => $records['root_id'],
            'origin_id' => $records['origin_id'],
            'project_report_type_mapping_id' => $records['project_report_type_mapping_id'],
            'approved_date' => $records['approved_date'],
            'revision' => $records['revision'],
            'submitted_by' => $records['submitted_by'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            'title' => $records['title'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_types')->insert([
            'is_locked' => $records['is_locked'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'title' => $records['title'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectReportUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_report_user_permissions')->insert([
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'identifier' => $records['identifier'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'project_report_type_id' => $records['project_report_type_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectRoles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_roles')->insert([
            'project_id' => $records['project_id'],
            'contract_group_id' => $records['contract_group_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForInformationMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_information_messages')->insert([
            'deleted_at' => $records['deleted_at'],
            'document_control_object_id' => $records['document_control_object_id'],
            'sequence_number' => $records['sequence_number'],
            'composed_by' => $records['composed_by'],
            'reply_deadline' => $records['reply_deadline'],
            'type' => $records['type'],
            'response_to' => $records['response_to'],
            'cost_impact' => $records['cost_impact'],
            'schedule_impact' => $records['schedule_impact'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'content' => $records['content'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectTrackRecordSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_track_record_settings')->insert([
            'project_detail_attachments' => $records['project_detail_attachments'],
            'project_quality_achievement_attachments' => $records['project_quality_achievement_attachments'],
            'project_award_recognition_attachments' => $records['project_award_recognition_attachments'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function propertyDevelopers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('property_developers')->insert([
            'hidden' => $records['hidden'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function purgedVendors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('purged_vendors')->insert([
            'purged_at' => $records['purged_at'],
            'name' => $records['name'],
            'reference_no' => $records['reference_no'],
            'email' => $records['email'],
            'telephone_number' => $records['telephone_number'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForInspectionReplies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_inspection_replies')->insert([
            'updated_at' => $records['updated_at'],
            'request_id' => $records['request_id'],
            'inspection_id' => $records['inspection_id'],
            'ready_date' => $records['ready_date'],
            'completed_date' => $records['completed_date'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'comments' => $records['comments'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationCategoryKpiLimitUpdateLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_variation_category_kpi_limit_update_logs')->insert([
            'request_for_variation_category_id' => $records['request_for_variation_category_id'],
            'kpi_limit' => $records['kpi_limit'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationContractAndContingencySum()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_variation_contract_and_contingency_sum')->insert([
            'project_id' => $records['project_id'],
            'original_contract_sum' => $records['original_contract_sum'],
            'contingency_sum' => $records['contingency_sum'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'contract_sum_includes_contingency_sum' => $records['contract_sum_includes_contingency_sum'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_variation_categories')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'kpi_limit' => $records['kpi_limit'],
            'name' => $records['name'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForInspections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_inspections')->insert([
            'project_id' => $records['project_id'],
            'location_id' => $records['location_id'],
            'inspection_list_category_id' => $records['inspection_list_category_id'],
            'submitted_by' => $records['submitted_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projectSectionalCompletionDates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('project_sectional_completion_dates')->insert([
            'project_id' => $records['project_id'],
            'sectional_completion_date' => $records['sectional_completion_date'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForInspectionInspections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_inspection_inspections')->insert([
            'updated_at' => $records['updated_at'],
            'request_id' => $records['request_id'],
            'created_at' => $records['created_at'],
            'inspected_at' => $records['inspected_at'],
            'status' => $records['status'],
            'sequence_number' => $records['sequence_number'],
            'created_by' => $records['created_by'],
            'comments' => $records['comments'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationActionLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_variation_action_logs')->insert([
            'updated_at' => $records['updated_at'],
            'request_for_variation_id' => $records['request_for_variation_id'],
            'user_id' => $records['user_id'],
            'permission_module_id' => $records['permission_module_id'],
            'action_type' => $records['action_type'],
            'verifier' => $records['verifier'],
            'approved' => $records['approved'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_variation_files')->insert([
            'request_for_variation_id' => $records['request_for_variation_id'],
            'cabinet_file_id' => $records['cabinet_file_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'filename' => $records['filename'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function rejectedMaterials()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('rejected_materials')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationUserPermissionGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_variation_user_permission_groups')->insert([
            'project_id' => $records['project_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_variations')->insert([
            'deleted_by' => $records['deleted_by'],
            'rfv_number' => $records['rfv_number'],
            'project_id' => $records['project_id'],
            'request_for_variation_user_permission_group_id' => $records['request_for_variation_user_permission_group_id'],
            'approved_category_amount' => $records['approved_category_amount'],
            'deleted_at' => $records['deleted_at'],
            'request_for_variation_category_id' => $records['request_for_variation_category_id'],
            'nett_omission_addition' => $records['nett_omission_addition'],
            'initiated_by' => $records['initiated_by'],
            'status' => $records['status'],
            'permission_module_in_charge' => $records['permission_module_in_charge'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'accumulative_approved_rfv_amount' => $records['accumulative_approved_rfv_amount'],
            'proposed_rfv_amount' => $records['proposed_rfv_amount'],
            'submitted_by' => $records['submitted_by'],
            'ai_number' => $records['ai_number'],
            'description' => $records['description'],
            'reasons_for_variation' => $records['reasons_for_variation'],
            'time_implication' => $records['time_implication'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function scheduledMaintenance()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('scheduled_maintenance')->insert([
            'updated_at' => $records['updated_at'],
            'is_under_maintenance' => $records['is_under_maintenance'],
            'created_at' => $records['created_at'],
            'created_by' => $records['created_by'],
            'start_time' => $records['start_time'],
            'end_time' => $records['end_time'],
            'message' => $records['message'],
            'image' => $records['image'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function sentTenderRemindersLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('sent_tender_reminders_log')->insert([
            'sent_by' => $records['sent_by'],
            'tender_id' => $records['tender_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementMcar()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_mcar')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'contractor_id' => $records['contractor_id'],
            'project_id' => $records['project_id'],
            'site_management_defect_id' => $records['site_management_defect_id'],
            'submitted_user_id' => $records['submitted_user_id'],
            'mcar_number' => $records['mcar_number'],
            'work_description' => $records['work_description'],
            'remark' => $records['remark'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestsForInspection()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('requests_for_inspection')->insert([
            'project_id' => $records['project_id'],
            'reference_number' => $records['reference_number'],
            'created_by' => $records['created_by'],
            'status' => $records['status'],
            'ready_date' => $records['ready_date'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'inspection_reference' => $records['inspection_reference'],
            'subject' => $records['subject'],
            'description' => $records['description'],
            'location' => $records['location'],
            'works' => $records['works'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementMcarFormResponses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_mcar_form_responses')->insert([
            'verifier_id' => $records['verifier_id'],
            'applicable' => $records['applicable'],
            'commitment_date' => $records['commitment_date'],
            'verified_at' => $records['verified_at'],
            'submitted_user_id' => $records['submitted_user_id'],
            'site_management_defect_id' => $records['site_management_defect_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'verified' => $records['verified'],
            'satisfactory' => $records['satisfactory'],
            'reinspection_date' => $records['reinspection_date'],
            'site_management_mcar_id' => $records['site_management_mcar_id'],
            'cause' => $records['cause'],
            'action' => $records['action'],
            'comment' => $records['comment'],
            'corrective' => $records['corrective'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryGeneralFormResponses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_site_diary_general_form_responses')->insert([
            'status' => $records['status'],
            'general_date' => $records['general_date'],
            'machinery_backhoe' => $records['machinery_backhoe'],
            'machinery_crane' => $records['machinery_crane'],
            'rejected_material_id' => $records['rejected_material_id'],
            'submitted_by' => $records['submitted_by'],
            'project_id' => $records['project_id'],
            'submitted_for_approval_by' => $records['submitted_for_approval_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'general_physical_progress' => $records['general_physical_progress'],
            'general_plan_progress' => $records['general_plan_progress'],
            'weather_id' => $records['weather_id'],
            'labour_project_manager' => $records['labour_project_manager'],
            'labour_site_agent' => $records['labour_site_agent'],
            'labour_supervisor' => $records['labour_supervisor'],
            'machinery_excavator' => $records['machinery_excavator'],
            'general_time_in' => $records['general_time_in'],
            'general_time_out' => $records['general_time_out'],
            'general_day' => $records['general_day'],
            'visitor_time_in' => $records['visitor_time_in'],
            'visitor_time_out' => $records['visitor_time_out'],
            'weather_time_from' => $records['weather_time_from'],
            'weather_time_to' => $records['weather_time_to'],
            'visitor_name' => $records['visitor_name'],
            'visitor_company_name' => $records['visitor_company_name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryLabours()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_site_diary_labours')->insert([
            'labour_id' => $records['labour_id'],
            'site_diary_id' => $records['site_diary_id'],
            'value' => $records['value'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function requestForVariationUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('request_for_variation_user_permissions')->insert([
            'user_id' => $records['user_id'],
            'module_id' => $records['module_id'],
            'is_editor' => $records['is_editor'],
            'added_by' => $records['added_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'can_view_cost_estimate' => $records['can_view_cost_estimate'],
            'can_view_vo_report' => $records['can_view_vo_report'],
            'request_for_variation_user_permission_group_id' => $records['request_for_variation_user_permission_group_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function riskRegisterMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('risk_register_messages')->insert([
            'document_control_object_id' => $records['document_control_object_id'],
            'sequence_number' => $records['sequence_number'],
            'composed_by' => $records['composed_by'],
            'reply_deadline' => $records['reply_deadline'],
            'detectability' => $records['detectability'],
            'importance' => $records['importance'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            'type' => $records['type'],
            'response_to' => $records['response_to'],
            'probability' => $records['probability'],
            'status' => $records['status'],
            'impact' => $records['impact'],
            'content' => $records['content'],
            'trigger_event' => $records['trigger_event'],
            'risk_response' => $records['risk_response'],
            'contingency_plan' => $records['contingency_plan'],
            'category' => $records['category'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementDefectBackchargeDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_defect_backcharge_details')->insert([
            'machinery' => $records['machinery'],
            'material' => $records['material'],
            'labour' => $records['labour'],
            'total' => $records['total'],
            'user_id' => $records['user_id'],
            'site_management_defect_id' => $records['site_management_defect_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'status_id' => $records['status_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementDefectFormResponses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_defect_form_responses')->insert([
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'response_identifier' => $records['response_identifier'],
            'site_management_defect_id' => $records['site_management_defect_id'],
            'user_id' => $records['user_id'],
            'remark' => $records['remark'],
            'path_to_photo' => $records['path_to_photo'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryWeathers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_site_diary_weathers')->insert([
            'site_diary_id' => $records['site_diary_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'weather_id' => $records['weather_id'],
            'weather_time_from' => $records['weather_time_from'],
            'weather_time_to' => $records['weather_time_to'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_user_permissions')->insert([
            'module_identifier' => $records['module_identifier'],
            'user_id' => $records['user_id'],
            'project_id' => $records['project_id'],
            'site' => $records['site'],
            'qa_qc_client' => $records['qa_qc_client'],
            'pm' => $records['pm'],
            'qs' => $records['qs'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'is_editor' => $records['is_editor'],
            'is_viewer' => $records['is_viewer'],
            'is_rate_editor' => $records['is_rate_editor'],
            'is_verifier' => $records['is_verifier'],
            'is_submitter' => $records['is_submitter'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function subsidiaries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('subsidiaries')->insert([
            'company_id' => $records['company_id'],
            'updated_at' => $records['updated_at'],
            'parent_id' => $records['parent_id'],
            'created_at' => $records['created_at'],
            'name' => $records['name'],
            'identifier' => $records['identifier'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function subsidiaryApportionmentRecords()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('subsidiary_apportionment_records')->insert([
            'subsidiary_id' => $records['subsidiary_id'],
            'apportionment_type_id' => $records['apportionment_type_id'],
            'value' => $records['value'],
            'is_locked' => $records['is_locked'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function supplierCreditFacilities()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('supplier_credit_facilities')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'vendor_registration_id' => $records['vendor_registration_id'],
            'supplier_name' => $records['supplier_name'],
            'credit_facilities' => $records['credit_facilities'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryMachinery()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_site_diary_machinery')->insert([
            'machinery_id' => $records['machinery_id'],
            'site_diary_id' => $records['site_diary_id'],
            'value' => $records['value'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function systemModuleElements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('system_module_elements')->insert([
            'has_attachments' => $records['has_attachments'],
            'element_definition_id' => $records['element_definition_id'],
            'updated_at' => $records['updated_at'],
            'is_key_information' => $records['is_key_information'],
            'created_at' => $records['created_at'],
            'label' => $records['label'],
            'instructions' => $records['instructions'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function supplierCreditFacilitySettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('supplier_credit_facility_settings')->insert([
            'has_attachments' => $records['has_attachments'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function systemModuleConfigurations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('system_module_configurations')->insert([
            'module_id' => $records['module_id'],
            'is_enabled' => $records['is_enabled'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryRejectedMaterials()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_site_diary_rejected_materials')->insert([
            'rejected_material_id' => $records['rejected_material_id'],
            'site_diary_id' => $records['site_diary_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementSiteDiaryVisitors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_site_diary_visitors')->insert([
            'site_diary_id' => $records['site_diary_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'visitor_time_out' => $records['visitor_time_out'],
            'visitor_name' => $records['visitor_name'],
            'visitor_company_name' => $records['visitor_company_name'],
            'visitor_time_in' => $records['visitor_time_in'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function structuredDocuments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('structured_documents')->insert([
            'is_template' => $records['is_template'],
            'object_id' => $records['object_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'margin_top' => $records['margin_top'],
            'margin_bottom' => $records['margin_bottom'],
            'margin_left' => $records['margin_left'],
            'margin_right' => $records['margin_right'],
            'font_size' => $records['font_size'],
            'title' => $records['title'],
            'heading' => $records['heading'],
            'footer_text' => $records['footer_text'],
            'object_type' => $records['object_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function systemSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('system_settings')->insert([
            'allow_other_business_entity_types' => $records['allow_other_business_entity_types'],
            'allow_other_property_developers' => $records['allow_other_property_developers'],
            'allow_other_vpe_project_removal_reasons' => $records['allow_other_vpe_project_removal_reasons'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationResponseLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('technical_evaluation_response_log')->insert([
            'company_id' => $records['company_id'],
            'set_reference_id' => $records['set_reference_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('technical_evaluations')->insert([
            'tender_id' => $records['tender_id'],
            'targeted_date_of_award' => $records['targeted_date_of_award'],
            'submitted_by' => $records['submitted_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function templateTenderDocumentFolders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('template_tender_document_folders')->insert([
            'folder_type' => $records['folder_type'],
            'root_id' => $records['root_id'],
            'parent_id' => $records['parent_id'],
            'lft' => $records['lft'],
            'rgt' => $records['rgt'],
            'depth' => $records['depth'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderDocumentDownloadLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_document_download_logs')->insert([
            'tender_document_id' => $records['tender_document_id'],
            'company_id' => $records['company_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderCallingTenderInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_calling_tender_information')->insert([
            'tender_id' => $records['tender_id'],
            'date_of_calling_tender' => $records['date_of_calling_tender'],
            'date_of_closing_tender' => $records['date_of_closing_tender'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'disable_tender_rates_submission' => $records['disable_tender_rates_submission'],
            'technical_tender_closing_date' => $records['technical_tender_closing_date'],
            'allow_contractor_propose_own_completion_period' => $records['allow_contractor_propose_own_completion_period'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tags()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tags')->insert([
            'category' => $records['category'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationSetReferences()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('technical_evaluation_set_references')->insert([
            'set_id' => $records['set_id'],
            'work_category_id' => $records['work_category_id'],
            'contract_limit_id' => $records['contract_limit_id'],
            'project_id' => $records['project_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'hidden' => $records['hidden'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('technical_evaluation_attachments')->insert([
            'upload_id' => $records['upload_id'],
            'company_id' => $records['company_id'],
            'item_id' => $records['item_id'],
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'filename' => $records['filename'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('technical_evaluation_items')->insert([
            'updated_at' => $records['updated_at'],
            'parent_id' => $records['parent_id'],
            'value' => $records['value'],
            'type' => $records['type'],
            'compulsory' => $records['compulsory'],
            'created_at' => $records['created_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationTendererOptions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('technical_evaluation_tenderer_options')->insert([
            'option_id' => $records['option_id'],
            'item_id' => $records['item_id'],
            'company_id' => $records['company_id'],
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationVerifierLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('technical_evaluation_verifier_logs')->insert([
            'tender_id' => $records['tender_id'],
            'user_id' => $records['user_id'],
            'type' => $records['type'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function templateTenderDocumentFolderWorkCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('template_tender_document_folder_work_category')->insert([
            'template_tender_document_folder_id' => $records['template_tender_document_folder_id'],
            'work_category_id' => $records['work_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function templateTenderDocumentFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('template_tender_document_files')->insert([
            'work_category_id' => $records['work_category_id'],
            'updated_at' => $records['updated_at'],
            'cabinet_file_id' => $records['cabinet_file_id'],
            'folder_id' => $records['folder_id'],
            'parent_id' => $records['parent_id'],
            'created_at' => $records['created_at'],
            'filename' => $records['filename'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function templateTenderDocumentFilesRolesReadonly()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('template_tender_document_files_roles_readonly')->insert([
            'template_tender_document_file_id' => $records['template_tender_document_file_id'],
            'contract_group_id' => $records['contract_group_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderAlternativesPosition()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_alternatives_position')->insert([
            'position' => $records['position'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'form_of_tender_id' => $records['form_of_tender_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderCallingTenderInformationUser()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_calling_tender_information_user')->insert([
            'tender_calling_tender_information_id' => $records['tender_calling_tender_information_id'],
            'user_id' => $records['user_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderInterviewInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_interview_information')->insert([
            'tender_id' => $records['tender_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'date_and_time' => $records['date_and_time'],
            'contract_group_id' => $records['contract_group_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderLotInformationUser()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_lot_information_user')->insert([
            'tender_lot_information_id' => $records['tender_lot_information_id'],
            'user_id' => $records['user_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderRotInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_rot_information')->insert([
            'tender_id' => $records['tender_id'],
            'proposed_date_of_calling_tender' => $records['proposed_date_of_calling_tender'],
            'proposed_date_of_closing_tender' => $records['proposed_date_of_closing_tender'],
            'target_date_of_site_possession' => $records['target_date_of_site_possession'],
            'budget' => $records['budget'],
            'consultant_estimates' => $records['consultant_estimates'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'completion_period' => $records['completion_period'],
            'project_incentive_percentage' => $records['project_incentive_percentage'],
            'allow_contractor_propose_own_completion_period' => $records['allow_contractor_propose_own_completion_period'],
            'technical_evaluation_required' => $records['technical_evaluation_required'],
            'contract_limit_id' => $records['contract_limit_id'],
            'completion_period_metric' => $records['completion_period_metric'],
            'disable_tender_rates_submission' => $records['disable_tender_rates_submission'],
            'procurement_method_id' => $records['procurement_method_id'],
            'technical_tender_closing_date' => $records['technical_tender_closing_date'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderRotInformationUser()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_rot_information_user')->insert([
            'tender_rot_information_id' => $records['tender_rot_information_id'],
            'user_id' => $records['user_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderDocumentFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_document_files')->insert([
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'cabinet_file_id' => $records['cabinet_file_id'],
            'tender_document_folder_id' => $records['tender_document_folder_id'],
            'revision' => $records['revision'],
            'parent_id' => $records['parent_id'],
            'filename' => $records['filename'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderLotInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_lot_information')->insert([
            'tender_id' => $records['tender_id'],
            'date_of_calling_tender' => $records['date_of_calling_tender'],
            'date_of_closing_tender' => $records['date_of_closing_tender'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'completion_period' => $records['completion_period'],
            'project_incentive_percentage' => $records['project_incentive_percentage'],
            'allow_contractor_propose_own_completion_period' => $records['allow_contractor_propose_own_completion_period'],
            'technical_evaluation_required' => $records['technical_evaluation_required'],
            'contract_limit_id' => $records['contract_limit_id'],
            'disable_tender_rates_submission' => $records['disable_tender_rates_submission'],
            'procurement_method_id' => $records['procurement_method_id'],
            'technical_tender_closing_date' => $records['technical_tender_closing_date'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderDocumentFilesRolesReadonly()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_document_files_roles_readonly')->insert([
            'tender_document_file_id' => $records['tender_document_file_id'],
            'contract_group_id' => $records['contract_group_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderDocumentFolders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_document_folders')->insert([
            'folder_type' => $records['folder_type'],
            'root_id' => $records['root_id'],
            'parent_id' => $records['parent_id'],
            'lft' => $records['lft'],
            'rgt' => $records['rgt'],
            'depth' => $records['depth'],
            'priority' => $records['priority'],
            'project_id' => $records['project_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'system_generated_folder' => $records['system_generated_folder'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderFormVerifierLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_form_verifier_logs')->insert([
            'loggable_id' => $records['loggable_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'user_id' => $records['user_id'],
            'type' => $records['type'],
            'loggable_type' => $records['loggable_type'],
            'verifier_remark' => $records['verifier_remark'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderInterviews()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_interviews')->insert([
            'tender_interview_information_id' => $records['tender_interview_information_id'],
            'tender_id' => $records['tender_id'],
            'company_id' => $records['company_id'],
            'updated_at' => $records['updated_at'],
            'date_and_time' => $records['date_and_time'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'venue' => $records['venue'],
            'key' => $records['key'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderReminders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_reminders')->insert([
            'tender_stage' => $records['tender_stage'],
            'tender_id' => $records['tender_id'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderUserTechnicalEvaluationVerifier()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_user_technical_evaluation_verifier')->insert([
            'tender_id' => $records['tender_id'],
            'user_id' => $records['user_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderUserVerifierOpenTender()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_user_verifier_open_tender')->insert([
            'tender_id' => $records['tender_id'],
            'user_id' => $records['user_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderUserVerifierRetender()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_user_verifier_retender')->insert([
            'tender_id' => $records['tender_id'],
            'user_id' => $records['user_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tendererTechnicalEvaluationInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tenderer_technical_evaluation_information')->insert([
            'shortlisted' => $records['shortlisted'],
            'company_id' => $records['company_id'],
            'tender_id' => $records['tender_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tendererTechnicalEvaluationInformationLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tenderer_technical_evaluation_information_log')->insert([
            'information_id' => $records['information_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function themeSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('theme_settings')->insert([
            'active' => $records['active'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'logo2' => $records['logo2'],
            'bg_image' => $records['bg_image'],
            'logo1' => $records['logo1'],
            'theme_colour1' => $records['theme_colour1'],
            'theme_colour2' => $records['theme_colour2'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function users()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('users')->insert([
            'is_gp_admin' => $records['is_gp_admin'],
            'company_id' => $records['company_id'],
            'is_admin' => $records['is_admin'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'account_blocked_status' => $records['account_blocked_status'],
            'allow_access_to_buildspace' => $records['allow_access_to_buildspace'],
            'password_updated_at' => $records['password_updated_at'],
            'purge_date' => $records['purge_date'],
            'allow_access_to_gp' => $records['allow_access_to_gp'],
            'confirmed' => $records['confirmed'],
            'is_super_admin' => $records['is_super_admin'],
            'name' => $records['name'],
            'contact_number' => $records['contact_number'],
            'username' => $records['username'],
            'email' => $records['email'],
            'password' => $records['password'],
            'confirmation_code' => $records['confirmation_code'],
            'remember_token' => $records['remember_token'],
            'gp_access_token' => $records['gp_access_token'],
            'designation' => $records['designation'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function userCompanyLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('user_company_log')->insert([
            'user_id' => $records['user_id'],
            'company_id' => $records['company_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function trackRecordProjects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('track_record_projects')->insert([
            'country_id' => $records['country_id'],
            'conquas_year_of_achievement' => $records['conquas_year_of_achievement'],
            'year_of_recognition_awards' => $records['year_of_recognition_awards'],
            'vendor_registration_id' => $records['vendor_registration_id'],
            'vendor_category_id' => $records['vendor_category_id'],
            'shassic_score' => $records['shassic_score'],
            'project_amount' => $records['project_amount'],
            'property_developer_id' => $records['property_developer_id'],
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'type' => $records['type'],
            'year_of_site_possession' => $records['year_of_site_possession'],
            'year_of_completion' => $records['year_of_completion'],
            'has_qlassic_or_conquas_score' => $records['has_qlassic_or_conquas_score'],
            'qlassic_year_of_achievement' => $records['qlassic_year_of_achievement'],
            'title' => $records['title'],
            'conquas_score' => $records['conquas_score'],
            'property_developer_text' => $records['property_developer_text'],
            'project_amount_remarks' => $records['project_amount_remarks'],
            'qlassic_score' => $records['qlassic_score'],
            'awards_received' => $records['awards_received'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_categories')->insert([
            'updated_at' => $records['updated_at'],
            'contract_group_category_id' => $records['contract_group_category_id'],
            'created_at' => $records['created_at'],
            'target' => $records['target'],
            'hidden' => $records['hidden'],
            'name' => $records['name'],
            'code' => $records['code'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorDetailSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_detail_settings')->insert([
            'updated_at' => $records['updated_at'],
            'created_at' => $records['created_at'],
            'contract_group_category_instructions' => $records['contract_group_category_instructions'],
            'vendor_category_instructions' => $records['vendor_category_instructions'],
            'contact_person_instructions' => $records['contact_person_instructions'],
            'reference_number_instructions' => $records['reference_number_instructions'],
            'tax_registration_number_instructions' => $records['tax_registration_number_instructions'],
            'email_instructions' => $records['email_instructions'],
            'telephone_instructions' => $records['telephone_instructions'],
            'fax_instructions' => $records['fax_instructions'],
            'country_instructions' => $records['country_instructions'],
            'state_instructions' => $records['state_instructions'],
            'company_status_instructions' => $records['company_status_instructions'],
            'bumiputera_equity_instructions' => $records['bumiputera_equity_instructions'],
            'non_bumiputera_equity_instructions' => $records['non_bumiputera_equity_instructions'],
            'foreigner_equity_instructions' => $records['foreigner_equity_instructions'],
            'cidb_grade_instructions' => $records['cidb_grade_instructions'],
            'bim_level_instructions' => $records['bim_level_instructions'],
            'name_instructions' => $records['name_instructions'],
            'address_instructions' => $records['address_instructions'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tenders')->insert([
            'technical_evaluation_status' => $records['technical_evaluation_status'],
            'project_id' => $records['project_id'],
            'count' => $records['count'],
            'current_form_type' => $records['current_form_type'],
            'tender_starting_date' => $records['tender_starting_date'],
            'tender_closing_date' => $records['tender_closing_date'],
            'retender_status' => $records['retender_status'],
            'retender_verification_status' => $records['retender_verification_status'],
            'open_tender_status' => $records['open_tender_status'],
            'open_tender_verification_status' => $records['open_tender_verification_status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'validity_period_in_days' => $records['validity_period_in_days'],
            'technical_evaluation_verification_status' => $records['technical_evaluation_verification_status'],
            'technical_tender_closing_date' => $records['technical_tender_closing_date'],
            'currently_selected_tenderer_id' => $records['currently_selected_tenderer_id'],
            'request_retender_at' => $records['request_retender_at'],
            'request_retender_by' => $records['request_retender_by'],
            'request_retender_remarks' => $records['request_retender_remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function uploads()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('uploads')->insert([
            'size' => $records['size'],
            'user_id' => $records['user_id'],
            'parent_id' => $records['parent_id'],
            'deleted_at' => $records['deleted_at'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'filename' => $records['filename'],
            'path' => $records['path'],
            'mobile_sync_uuid' => $records['mobile_sync_uuid'],
            'extension' => $records['extension'],
            'mimetype' => $records['mimetype'],
            'original_file_name' => $records['original_file_name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function userLogins()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('user_logins')->insert([
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function userSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('user_settings')->insert([
            'user_id' => $records['user_id'],
            'language_id' => $records['language_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function usersCompanyVerificationPrivileges()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('users_company_verification_privileges')->insert([
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorCategoryTemporaryRecords()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_category_temporary_records')->insert([
            'vendor_registration_id' => $records['vendor_registration_id'],
            'vendor_category_id' => $records['vendor_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorCategoryVendorWorkCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_category_vendor_work_category')->insert([
            'vendor_category_id' => $records['vendor_category_id'],
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorEvaluationCycleScores()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_evaluation_cycle_scores')->insert([
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'company_id' => $records['company_id'],
            'vendor_performance_evaluation_cycle_id' => $records['vendor_performance_evaluation_cycle_id'],
            'score' => $records['score'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deliberated_score' => $records['deliberated_score'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorEvaluationScores()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_evaluation_scores')->insert([
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'company_id' => $records['company_id'],
            'vendor_performance_evaluation_id' => $records['vendor_performance_evaluation_id'],
            'score' => $records['score'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorManagementInstructionSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_management_instruction_settings')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'supplier_credit_facilities' => $records['supplier_credit_facilities'],
            'payment' => $records['payment'],
            'vendor_pre_qualifications' => $records['vendor_pre_qualifications'],
            'company_personnel' => $records['company_personnel'],
            'project_track_record' => $records['project_track_record'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationFormChangeLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_form_change_logs')->insert([
            'user_id' => $records['user_id'],
            'vendor_performance_evaluation_setup_id' => $records['vendor_performance_evaluation_setup_id'],
            'old_template_node_id' => $records['old_template_node_id'],
            'new_template_node_id' => $records['new_template_node_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorManagementGradeLevels()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_management_grade_levels')->insert([
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'score_upper_limit' => $records['score_upper_limit'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'description' => $records['description'],
            'definition' => $records['definition'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationModuleParameters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_module_parameters')->insert([
            'default_time_frame_for_vpe_cycle_value' => $records['default_time_frame_for_vpe_cycle_value'],
            'default_time_frame_for_vpe_cycle_unit' => $records['default_time_frame_for_vpe_cycle_unit'],
            'default_time_frame_for_vpe_submission_value' => $records['default_time_frame_for_vpe_submission_value'],
            'default_time_frame_for_vpe_submission_unit' => $records['default_time_frame_for_vpe_submission_unit'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'attachments_required' => $records['attachments_required'],
            'attachments_required_score_threshold' => $records['attachments_required_score_threshold'],
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            'passing_score' => $records['passing_score'],
            'email_reminder_before_cycle_end_date' => $records['email_reminder_before_cycle_end_date'],
            'email_reminder_before_cycle_end_date_value' => $records['email_reminder_before_cycle_end_date_value'],
            'email_reminder_before_cycle_end_date_unit' => $records['email_reminder_before_cycle_end_date_unit'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationProjectRemovalReasons()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_project_removal_reasons')->insert([
            'hidden' => $records['hidden'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationSubmissionReminderSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_submission_reminder_settings')->insert([
            'number_of_days_before' => $records['number_of_days_before'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorManagementGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_management_grades')->insert([
            'updated_by' => $records['updated_by'],
            'is_template' => $records['is_template'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorManagementUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_management_user_permissions')->insert([
            'user_id' => $records['user_id'],
            'type' => $records['type'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationCompanyFormEvaluationLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_company_form_evaluation_logs')->insert([
            'vendor_performance_evaluation_company_form_id' => $records['vendor_performance_evaluation_company_form_id'],
            'action_type' => $records['action_type'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationCompanyForms()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_company_forms')->insert([
            'submitted_for_approval_by' => $records['submitted_for_approval_by'],
            'vendor_performance_evaluation_id' => $records['vendor_performance_evaluation_id'],
            'company_id' => $records['company_id'],
            'weighted_node_id' => $records['weighted_node_id'],
            'evaluator_company_id' => $records['evaluator_company_id'],
            'status_id' => $records['status_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'deleted_at' => $records['deleted_at'],
            'score' => $records['score'],
            'evaluator_remarks' => $records['evaluator_remarks'],
            'processor_remarks' => $records['processor_remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationFormChangeRequests()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_form_change_requests')->insert([
            'deleted_at' => $records['deleted_at'],
            'user_id' => $records['user_id'],
            'vendor_performance_evaluation_setup_id' => $records['vendor_performance_evaluation_setup_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationProcessorEditDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_processor_edit_details')->insert([
            'vendor_performance_evaluation_processor_edit_log_id' => $records['vendor_performance_evaluation_processor_edit_log_id'],
            'weighted_node_id' => $records['weighted_node_id'],
            'previous_score_id' => $records['previous_score_id'],
            'is_previous_node_excluded' => $records['is_previous_node_excluded'],
            'current_score_id' => $records['current_score_id'],
            'is_current_node_excluded' => $records['is_current_node_excluded'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationProcessorEditLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_processor_edit_logs')->insert([
            'vendor_performance_evaluation_company_form_id' => $records['vendor_performance_evaluation_company_form_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationRemovalRequests()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_removal_requests')->insert([
            'company_id' => $records['company_id'],
            'vendor_performance_evaluation_id' => $records['vendor_performance_evaluation_id'],
            'user_id' => $records['user_id'],
            'vendor_performance_evaluation_project_removal_reason_id' => $records['vendor_performance_evaluation_project_removal_reason_id'],
            'deleted_at' => $records['deleted_at'],
            'removed_at' => $records['removed_at'],
            'action_by' => $records['action_by'],
            'evaluation_removed' => $records['evaluation_removed'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'vendor_performance_evaluation_project_removal_reason_text' => $records['vendor_performance_evaluation_project_removal_reason_text'],
            'request_remarks' => $records['request_remarks'],
            'dismissal_remarks' => $records['dismissal_remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationSetups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_setups')->insert([
            'vendor_performance_evaluation_id' => $records['vendor_performance_evaluation_id'],
            'company_id' => $records['company_id'],
            'template_node_id' => $records['template_node_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationTemplateForms()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_template_forms')->insert([
            'project_status_id' => $records['project_status_id'],
            'contract_group_category_id' => $records['contract_group_category_id'],
            'weighted_node_id' => $records['weighted_node_id'],
            'revision' => $records['revision'],
            'status_id' => $records['status_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'original_form_id' => $records['original_form_id'],
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            'current_selected_revision' => $records['current_selected_revision'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorProfileModuleParameters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_profile_module_parameters')->insert([
            'validity_period_of_active_vendor_in_avl_value' => $records['validity_period_of_active_vendor_in_avl_value'],
            'validity_period_of_active_vendor_in_avl_unit' => $records['validity_period_of_active_vendor_in_avl_unit'],
            'grace_period_of_expired_vendor_before_moving_to_dvl_value' => $records['grace_period_of_expired_vendor_before_moving_to_dvl_value'],
            'grace_period_of_expired_vendor_before_moving_to_dvl_unit' => $records['grace_period_of_expired_vendor_before_moving_to_dvl_unit'],
            'vendor_retain_period_in_wl_value' => $records['vendor_retain_period_in_wl_value'],
            'vendor_retain_period_in_wl_unit' => $records['vendor_retain_period_in_wl_unit'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'renewal_period_before_expiry_in_days' => $records['renewal_period_before_expiry_in_days'],
            'watch_list_nomineee_to_active_vendor_list_threshold_score' => $records['watch_list_nomineee_to_active_vendor_list_threshold_score'],
            'watch_list_nomineee_to_watch_list_threshold_score' => $records['watch_list_nomineee_to_watch_list_threshold_score'],
            'registration_price' => $records['registration_price'],
            'renewal_price' => $records['renewal_price'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorProfiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_profiles')->insert([
            'company_id' => $records['company_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationAndPrequalificationModuleParameters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_registration_and_prequalification_module_parameters')->insert([
            'notify_vendors_for_renewal_unit' => $records['notify_vendors_for_renewal_unit'],
            'valid_period_of_temp_login_acc_to_unreg_vendor_value' => $records['valid_period_of_temp_login_acc_to_unreg_vendor_value'],
            'valid_period_of_temp_login_acc_to_unreg_vendor_unit' => $records['valid_period_of_temp_login_acc_to_unreg_vendor_unit'],
            'allow_only_one_comp_to_reg_under_multi_vendor_group' => $records['allow_only_one_comp_to_reg_under_multi_vendor_group'],
            'allow_only_one_comp_to_reg_under_multi_vendor_category' => $records['allow_only_one_comp_to_reg_under_multi_vendor_category'],
            'vendor_reg_cert_generated_sent_to_successful_reg_vendor' => $records['vendor_reg_cert_generated_sent_to_successful_reg_vendor'],
            'notify_vendor_before_end_of_temp_acc_valid_period_value' => $records['notify_vendor_before_end_of_temp_acc_valid_period_value'],
            'notify_vendor_before_end_of_temp_acc_valid_period_unit' => $records['notify_vendor_before_end_of_temp_acc_valid_period_unit'],
            'period_retain_unsuccessful_reg_and_preq_submission_value' => $records['period_retain_unsuccessful_reg_and_preq_submission_value'],
            'period_retain_unsuccessful_reg_and_preq_submission_unit' => $records['period_retain_unsuccessful_reg_and_preq_submission_unit'],
            'start_period_retain_unsuccessful_reg_and_preq_submission_value' => $records['start_period_retain_unsuccessful_reg_and_preq_submission_value'],
            'notify_purge_data_before_end_period_for_unsuccessful_sub_value' => $records['notify_purge_data_before_end_period_for_unsuccessful_sub_value'],
            'notify_purge_data_before_end_period_for_unsuccessful_sub_unit' => $records['notify_purge_data_before_end_period_for_unsuccessful_sub_unit'],
            'retain_info_of_unsuccessfully_reg_vendor_after_data_purge' => $records['retain_info_of_unsuccessfully_reg_vendor_after_data_purge'],
            'retain_company_name' => $records['retain_company_name'],
            'retain_roc_number' => $records['retain_roc_number'],
            'retain_email' => $records['retain_email'],
            'retain_contact_number' => $records['retain_contact_number'],
            'retain_date_of_data_purging' => $records['retain_date_of_data_purging'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            'valid_submission_days' => $records['valid_submission_days'],
            'notify_vendors_for_renewal_value' => $records['notify_vendors_for_renewal_value'],
            'vendor_declaration' => $records['vendor_declaration'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationFormTemplateMappings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_registration_form_template_mappings')->insert([
            'contract_group_category_id' => $records['contract_group_category_id'],
            'business_entity_type_id' => $records['business_entity_type_id'],
            'dynamic_form_id' => $records['dynamic_form_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationSections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_registration_sections')->insert([
            'is_section_applicable' => $records['is_section_applicable'],
            'vendor_registration_id' => $records['vendor_registration_id'],
            'section' => $records['section'],
            'status_id' => $records['status_id'],
            'amendment_status' => $records['amendment_status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'amendment_remarks' => $records['amendment_remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationSubmissionLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_registration_submission_logs')->insert([
            'vendor_registration_id' => $records['vendor_registration_id'],
            'action_type' => $records['action_type'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluations')->insert([
            'vendor_performance_evaluation_cycle_id' => $records['vendor_performance_evaluation_cycle_id'],
            'project_id' => $records['project_id'],
            'project_status_id' => $records['project_status_id'],
            'status_id' => $records['status_id'],
            'person_in_charge_id' => $records['person_in_charge_id'],
            'start_date' => $records['start_date'],
            'end_date' => $records['end_date'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            'type' => $records['type'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'deleted_by' => $records['deleted_by'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluators()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluators')->insert([
            'vendor_performance_evaluation_id' => $records['vendor_performance_evaluation_id'],
            'company_id' => $records['company_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPreQualificationSetups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_pre_qualification_setups')->insert([
            'vendor_category_id' => $records['vendor_category_id'],
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'pre_qualification_required' => $records['pre_qualification_required'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPreQualificationTemplateForms()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_pre_qualification_template_forms')->insert([
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'weighted_node_id' => $records['weighted_node_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'status_id' => $records['status_id'],
            'revision' => $records['revision'],
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPreQualificationVendorGroupGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_pre_qualification_vendor_group_grades')->insert([
            'contract_group_category_id' => $records['contract_group_category_id'],
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPreQualifications()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_pre_qualifications')->insert([
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'weighted_node_id' => $records['weighted_node_id'],
            'status_id' => $records['status_id'],
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            'vendor_registration_id' => $records['vendor_registration_id'],
            'template_form_id' => $records['template_form_id'],
            'deleted_at' => $records['deleted_at'],
            'score' => $records['score'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorProfileRemarks()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_profile_remarks')->insert([
            'created_by' => $records['created_by'],
            'vendor_profile_id' => $records['vendor_profile_id'],
            'updated_at' => $records['updated_at'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'content' => $records['content'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationPayments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_registration_payments')->insert([
            'company_id' => $records['company_id'],
            'payment_setting_id' => $records['payment_setting_id'],
            'running_number' => $records['running_number'],
            'currently_selected' => $records['currently_selected'],
            'submitted' => $records['submitted'],
            'paid' => $records['paid'],
            'successful' => $records['successful'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'submitted_date' => $records['submitted_date'],
            'paid_date' => $records['paid_date'],
            'successful_date' => $records['successful_date'],
            'status' => $records['status'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationProcessors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_registration_processors')->insert([
            'vendor_registration_id' => $records['vendor_registration_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weatherRecordReports()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('weather_record_reports')->insert([
            'weather_record_id' => $records['weather_record_id'],
            'created_by' => $records['created_by'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            'weather_status' => $records['weather_status'],
            'created_at' => $records['created_at'],
            'from_time' => $records['from_time'],
            'to_time' => $records['to_time'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorTypeChangeLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_type_change_logs')->insert([
            'vendor_id' => $records['vendor_id'],
            'old_type' => $records['old_type'],
            'new_type' => $records['new_type'],
            'vendor_evaluation_cycle_score_id' => $records['vendor_evaluation_cycle_score_id'],
            'watch_list_entry_date' => $records['watch_list_entry_date'],
            'watch_list_release_date' => $records['watch_list_release_date'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weightedNodeScores()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('weighted_node_scores')->insert([
            'amendments_required' => $records['amendments_required'],
            'node_id' => $records['node_id'],
            'updated_at' => $records['updated_at'],
            'value' => $records['value'],
            'is_selected' => $records['is_selected'],
            'created_at' => $records['created_at'],
            'name' => $records['name'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function workCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('work_categories')->insert([
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            'enabled' => $records['enabled'],
            'created_at' => $records['created_at'],
            'name' => $records['name'],
            'identifier' => $records['identifier'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function workSubcategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('work_subcategories')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weathers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('weathers')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorWorkCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_work_categories')->insert([
            'hidden' => $records['hidden'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            'code' => $records['code'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorWorkSubcategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_work_subcategories')->insert([
            'hidden' => $records['hidden'],
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            'code' => $records['code'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weightedNodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('weighted_nodes')->insert([
            'is_excluded' => $records['is_excluded'],
            'parent_id' => $records['parent_id'],
            'lft' => $records['lft'],
            'rgt' => $records['rgt'],
            'depth' => $records['depth'],
            'amendments_required' => $records['amendments_required'],
            'weight' => $records['weight'],
            'root_id' => $records['root_id'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendors')->insert([
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'company_id' => $records['company_id'],
            'type' => $records['type'],
            'vendor_evaluation_cycle_score_id' => $records['vendor_evaluation_cycle_score_id'],
            'is_qualified' => $records['is_qualified'],
            'watch_list_entry_date' => $records['watch_list_entry_date'],
            'watch_list_release_date' => $records['watch_list_release_date'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorWorkCategoryWorkCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_work_category_work_category')->insert([
            'vendor_work_category_id' => $records['vendor_work_category_id'],
            'work_category_id' => $records['work_category_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function verifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('verifiers')->insert([
            'deleted_at' => $records['deleted_at'],
            'verifier_id' => $records['verifier_id'],
            'object_id' => $records['object_id'],
            'days_to_verify' => $records['days_to_verify'],
            'sequence_number' => $records['sequence_number'],
            'approved' => $records['approved'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'verified_at' => $records['verified_at'],
            'start_at' => $records['start_at'],
            'object_type' => $records['object_type'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function weatherRecords()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('weather_records')->insert([
            'project_id' => $records['project_id'],
            'created_by' => $records['created_by'],
            'verified_by' => $records['verified_by'],
            'date' => $records['date'],
            'deleted_at' => $records['deleted_at'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'note' => $records['note'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function accessLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('access_log')->insert([
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'params' => $records['params'],
            'http_method' => $records['http_method'],
            'url' => $records['url'],
            'ip_address' => $records['ip_address'],
            'user_agent' => $records['user_agent'],
            'url_path' => $records['url_path'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function projects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('projects')->insert([
            'e_bidding' => $records['e_bidding'],
            'business_unit_id' => $records['business_unit_id'],
            'contract_id' => $records['contract_id'],
            'contractor_access_enabled' => $records['contractor_access_enabled'],
            'skipped_to_post_contract' => $records['skipped_to_post_contract'],
            'contractor_contractual_claim_access_enabled' => $records['contractor_contractual_claim_access_enabled'],
            'parent_project_id' => $records['parent_project_id'],
            'deleted_at' => $records['deleted_at'],
            'open_tender' => $records['open_tender'],
            'running_number' => $records['running_number'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'status_id' => $records['status_id'],
            'country_id' => $records['country_id'],
            'state_id' => $records['state_id'],
            'completion_date' => $records['completion_date'],
            'work_category_id' => $records['work_category_id'],
            'current_tender_status' => $records['current_tender_status'],
            'subsidiary_id' => $records['subsidiary_id'],
            'title' => $records['title'],
            'reference' => $records['reference'],
            'address' => $records['address'],
            'description' => $records['description'],
            'modified_currency_name' => $records['modified_currency_name'],
            'reference_suffix' => $records['reference_suffix'],
            'modified_currency_code' => $records['modified_currency_code'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function accountingReportExportLogDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('accounting_report_export_log_details')->insert([
            'accounting_report_export_log_id' => $records['accounting_report_export_log_id'],
            'project_code_setting_id' => $records['project_code_setting_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function interimClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('interim_claims')->insert([
            'project_id' => $records['project_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'month' => $records['month'],
            'year' => $records['year'],
            'issue_certificate_deadline' => $records['issue_certificate_deadline'],
            'amount_claimed' => $records['amount_claimed'],
            'amount_granted' => $records['amount_granted'],
            'claim_counter' => $records['claim_counter'],
            'status' => $records['status'],
            'claim_no' => $records['claim_no'],
            'note' => $records['note'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function architectInstructions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('architect_instructions')->insert([
            'updated_at' => $records['updated_at'],
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'deadline_to_comply' => $records['deadline_to_comply'],
            'status' => $records['status'],
            'steps' => $records['steps'],
            'reference' => $records['reference'],
            'instruction' => $records['instruction'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function aeSecondLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('ae_second_level_messages')->insert([
            'updated_at' => $records['updated_at'],
            'additional_expense_id' => $records['additional_expense_id'],
            'created_by' => $records['created_by'],
            'created_at' => $records['created_at'],
            'requested_new_deadline' => $records['requested_new_deadline'],
            'grant_different_deadline' => $records['grant_different_deadline'],
            'decision' => $records['decision'],
            'type' => $records['type'],
            'subject' => $records['subject'],
            'message' => $records['message'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('companies')->insert([
            'bim_level_id' => $records['bim_level_id'],
            'bumiputera_equity' => $records['bumiputera_equity'],
            'non_bumiputera_equity' => $records['non_bumiputera_equity'],
            'foreigner_equity' => $records['foreigner_equity'],
            'expiry_date' => $records['expiry_date'],
            'activation_date' => $records['activation_date'],
            'third_party_vendor_id' => $records['third_party_vendor_id'],
            'deactivation_date' => $records['deactivation_date'],
            'deactivated_at' => $records['deactivated_at'],
            'vendor_status' => $records['vendor_status'],
            'company_status' => $records['company_status'],
            'cidb_grade' => $records['cidb_grade'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'country_id' => $records['country_id'],
            'state_id' => $records['state_id'],
            'contract_group_category_id' => $records['contract_group_category_id'],
            'confirmed' => $records['confirmed'],
            'business_entity_type_id' => $records['business_entity_type_id'],
            'purge_date' => $records['purge_date'],
            'is_bumiputera' => $records['is_bumiputera'],
            'name' => $records['name'],
            'address' => $records['address'],
            'main_contact' => $records['main_contact'],
            'email' => $records['email'],
            'telephone_number' => $records['telephone_number'],
            'fax_number' => $records['fax_number'],
            'third_party_app_identifier' => $records['third_party_app_identifier'],
            'business_entity_type_name' => $records['business_entity_type_name'],
            'tax_registration_no' => $records['tax_registration_no'],
            'tax_registration_id' => $records['tax_registration_id'],
            'reference_no' => $records['reference_no'],
            'reference_id' => $records['reference_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function countries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('countries')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'fips' => $records['fips'],
            'country' => $records['country'],
            'continent' => $records['continent'],
            'currency_code' => $records['currency_code'],
            'currency_name' => $records['currency_name'],
            'phone_prefix' => $records['phone_prefix'],
            'postal_code' => $records['postal_code'],
            'languages' => $records['languages'],
            'geonameid' => $records['geonameid'],
            'iso' => $records['iso'],
            'iso3' => $records['iso3'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function states()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('states')->insert([
            'country_id' => $records['country_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            'timezone' => $records['timezone'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function clauseItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('clause_items')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'clause_id' => $records['clause_id'],
            'priority' => $records['priority'],
            'description' => $records['description'],
            'no' => $records['no'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementAttachmentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_attachment_settings')->insert([
            'updated_at' => $records['updated_at'],
            'consultant_management_contract_id' => $records['consultant_management_contract_id'],
            'mandatory' => $records['mandatory'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'title' => $records['title'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementOpenRfp()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_open_rfp')->insert([
            'consultant_management_rfp_revision_id' => $records['consultant_management_rfp_revision_id'],
            'status' => $records['status'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementApprovalDocumentSectionA()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_approval_document_section_a')->insert([
            'consultant_management_approval_document_id' => $records['consultant_management_approval_document_id'],
            'approving_authority' => $records['approving_authority'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_registrations')->insert([
            'company_id' => $records['company_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'submitted_at' => $records['submitted_at'],
            'unsuccessful_at' => $records['unsuccessful_at'],
            'revision' => $records['revision'],
            'deleted_at' => $records['deleted_at'],
            'submission_type' => $records['submission_type'],
            'processor_remarks' => $records['processor_remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function companyPersonnel()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('company_personnel')->insert([
            'type' => $records['type'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'vendor_registration_id' => $records['vendor_registration_id'],
            'years_of_experience' => $records['years_of_experience'],
            'amount_of_share' => $records['amount_of_share'],
            'holding_percentage' => $records['holding_percentage'],
            'amount_of_share_remarks' => $records['amount_of_share_remarks'],
            'holding_percentage_remarks' => $records['holding_percentage_remarks'],
            'name' => $records['name'],
            'identification_number' => $records['identification_number'],
            'email_address' => $records['email_address'],
            'contact_number' => $records['contact_number'],
            'years_of_experience_remarks' => $records['years_of_experience_remarks'],
            'designation' => $records['designation'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function consultantManagementCompanyRoles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('consultant_management_company_roles')->insert([
            'role' => $records['role'],
            'consultant_management_contract_id' => $records['consultant_management_contract_id'],
            'company_id' => $records['company_id'],
            'calling_rfp' => $records['calling_rfp'],
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function productTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('product_types')->insert([
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'title' => $records['title'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function developmentTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('development_types')->insert([
            'created_by' => $records['created_by'],
            'updated_by' => $records['updated_by'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'title' => $records['title'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function contractorWorkCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('contractor_work_category')->insert([
            'contractor_id' => $records['contractor_id'],
            'work_category_id' => $records['work_category_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function previousCpeGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('previous_cpe_grades')->insert([
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'grade' => $records['grade'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function dailyLabourReportLabourRates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('daily_labour_report_labour_rates')->insert([
            'labour_type' => $records['labour_type'],
            'normal_working_hours' => $records['normal_working_hours'],
            'normal_rate' => $records['normal_rate'],
            'ot_rate' => $records['ot_rate'],
            'normal_workers_total' => $records['normal_workers_total'],
            'ot_workers_total' => $records['ot_workers_total'],
            'ot_hours_total' => $records['ot_hours_total'],
            'total_cost' => $records['total_cost'],
            'daily_labour_report_id' => $records['daily_labour_report_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function emailNotificationRecipients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('email_notification_recipients')->insert([
            'email_notification_id' => $records['email_notification_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function externalApplicationClientModules()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('external_application_client_modules')->insert([
            'client_id' => $records['client_id'],
            'outbound_status' => $records['outbound_status'],
            'outbound_only_same_source' => $records['outbound_only_same_source'],
            'downstream_permission' => $records['downstream_permission'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'module' => $records['module'],
            'outbound_url_path' => $records['outbound_url_path'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function formColumnSections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('form_column_sections')->insert([
            'form_column_id' => $records['form_column_id'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function forumThreadUserSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('forum_thread_user_settings')->insert([
            'forum_thread_user_id' => $records['forum_thread_user_id'],
            'keep_me_posted' => $records['keep_me_posted'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractEarlyWarnings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('indonesia_civil_contract_early_warnings')->insert([
            'project_id' => $records['project_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'commencement_date' => $records['commencement_date'],
            'reference' => $records['reference'],
            'impact' => $records['impact'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function indonesiaCivilContractEwEot()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('indonesia_civil_contract_ew_eot')->insert([
            'indonesia_civil_contract_ew_id' => $records['indonesia_civil_contract_ew_id'],
            'indonesia_civil_contract_eot_id' => $records['indonesia_civil_contract_eot_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function letterOfAwardClauseCommentReadLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('letter_of_award_clause_comment_read_logs')->insert([
            'user_id' => $records['user_id'],
            'clause_comment_id' => $records['clause_comment_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function menus()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('menus')->insert([
            'contract_id' => $records['contract_id'],
            'priority' => $records['priority'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'name' => $records['name'],
            'icon_class' => $records['icon_class'],
            'route_name' => $records['route_name'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function siteManagementDefects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('site_management_defects')->insert([
            'status_id' => $records['status_id'],
            'count_reject' => $records['count_reject'],
            'project_structure_location_code_id' => $records['project_structure_location_code_id'],
            'pre_defined_location_code_id' => $records['pre_defined_location_code_id'],
            'contractor_id' => $records['contractor_id'],
            'defect_category_id' => $records['defect_category_id'],
            'defect_id' => $records['defect_id'],
            'bill_column_setting_id' => $records['bill_column_setting_id'],
            'unit' => $records['unit'],
            'pic_user_id' => $records['pic_user_id'],
            'project_id' => $records['project_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'mcar_status' => $records['mcar_status'],
            'submitted_by' => $records['submitted_by'],
            'mobile_sync_uuid' => $records['mobile_sync_uuid'],
            'remark' => $records['remark'],
            'path_to_defect_photo' => $records['path_to_defect_photo'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function modulePermissionSubsidiaries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('module_permission_subsidiaries')->insert([
            'module_permission_id' => $records['module_permission_id'],
            'subsidiary_id' => $records['subsidiary_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function notificationsCategoriesInGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('notifications_categories_in_groups')->insert([
            'category_id' => $records['category_id'],
            'group_id' => $records['group_id'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function objectPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('object_permissions')->insert([
            'updated_at' => $records['updated_at'],
            'user_id' => $records['user_id'],
            'object_id' => $records['object_id'],
            'is_editor' => $records['is_editor'],
            'created_at' => $records['created_at'],
            'object_type' => $records['object_type'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function openTenderAwardRecommendationReportEditLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('open_tender_award_recommendation_report_edit_logs')->insert([
            'open_tender_award_recommendation_id' => $records['open_tender_award_recommendation_id'],
            'user_id' => $records['user_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function structuredDocumentClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('structured_document_clauses')->insert([
            'updated_at' => $records['updated_at'],
            'is_editable' => $records['is_editable'],
            'parent_id' => $records['parent_id'],
            'priority' => $records['priority'],
            'structured_document_id' => $records['structured_document_id'],
            'created_at' => $records['created_at'],
            'content' => $records['content'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function technicalEvaluationAttachmentListItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('technical_evaluation_attachment_list_items')->insert([
            'set_reference_id' => $records['set_reference_id'],
            'compulsory' => $records['compulsory'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'description' => $records['description'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function tenderInterviewLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('tender_interview_logs')->insert([
            'user_id' => $records['user_id'],
            'interview_id' => $records['interview_id'],
            'status' => $records['status'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function trackRecordProjectVendorWorkSubcategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('track_record_project_vendor_work_subcategories')->insert([
            'track_record_project_id' => $records['track_record_project_id'],
            'vendor_work_subcategory_id' => $records['vendor_work_subcategory_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorPerformanceEvaluationCycles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_performance_evaluation_cycles')->insert([
            'vendor_management_grade_id' => $records['vendor_management_grade_id'],
            'start_date' => $records['start_date'],
            'end_date' => $records['end_date'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'is_completed' => $records['is_completed'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
            $msg = $e->getMessage(); // Provide the exception message
            $success = false;
        }
    
        // Return the response
        return Response::json([
            'success' => true,
            'data' => $msg,
        ], $httpResponseCode);
    }

    public function vendorRegistrationProcessorRemarks()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $msg = 'Data created successfully';
        $success = true;
        $httpResponseCode = 201;
        $records = \Input::all();
    
        try {
            // Insert a new record
            DB::table('vendor_registration_processor_remarks')->insert([
            'vendor_registration_processor_id' => $records['vendor_registration_processor_id'],
            'created_at' => $records['created_at'],
            'updated_at' => $records['updated_at'],
            'deleted_at' => $records['deleted_at'],
            'remarks' => $records['remarks'],
            ]);
        } catch (\Exception $e) {
            // If there's an error, catch the exception
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