<?php
namespace Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class GetDataApiController extends \BaseController
{
    protected $expectedToken = 'omkoFF3J2J6XywgbZF81Si5AK7uJNza6yos0FnrL5RdnTkLacsKS60LxcFxe6mPR';
    
    public function accountCodeSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('account_code_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'apportionment_type_id' => $item->apportionment_type_id,
                      'account_group_id' => $item->account_group_id,
                      'submitted_for_approval_by' => $item->submitted_for_approval_by,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'vendor_category_id' => $item->vendor_category_id,
                      'beneficiary_bank_account_number' => $item->beneficiary_bank_account_number,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function additionalElementValues()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('additional_element_values')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'element_value_id' => $item->element_value_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'value' => $item->value,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function additionalExpenses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('additional_expenses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'architect_instruction_id' => $item->architect_instruction_id,
                      'created_by' => $item->created_by,
                      'commencement_date_of_event' => $item->commencement_date_of_event,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'initial_estimate_of_claim' => $item->initial_estimate_of_claim,
                      'amount_claimed' => $item->amount_claimed,
                      'amount_granted' => $item->amount_granted,
                      'status' => $item->status,
                      'subject' => $item->subject,
                      'detailed_elaborations' => $item->detailed_elaborations,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function additionalExpenseInterimClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('additional_expense_interim_claims')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'additional_expense_id' => $item->additional_expense_id,
                      'interim_claim_id' => $item->interim_claim_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function aeContractorConfirmDelays()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('ae_contractor_confirm_delays')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'additional_expense_id' => $item->additional_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'date_on_which_delay_is_over' => $item->date_on_which_delay_is_over,
                      'deadline_to_submit_final_claim' => $item->deadline_to_submit_final_claim,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function aeFirstLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('ae_first_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'additional_expense_id' => $item->additional_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'decision' => $item->decision,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'details' => $item->details,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function aeFourthLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('ae_fourth_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'additional_expense_id' => $item->additional_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'grant_different_amount' => $item->grant_different_amount,
                      'decision' => $item->decision,
                      'type' => $item->type,
                      'locked' => $item->locked,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function accountingReportExportLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('accounting_report_export_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'claim_certificate_id' => $item->claim_certificate_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function accountingReportExportLogItemCodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('accounting_report_export_log_item_codes')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'accounting_report_export_log_detail_id' => $item->accounting_report_export_log_detail_id,
                      'item_code_setting_id' => $item->item_code_setting_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function acknowledgementLetters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('acknowledgement_letters')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'enable_letter' => $item->enable_letter,
                      'letter_content' => $item->letter_content,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function additionalExpenseClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('additional_expense_claims')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'additional_expense_id' => $item->additional_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'final_claim_amount' => $item->final_claim_amount,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function aeThirdLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('ae_third_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'additional_expense_id' => $item->additional_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deadline_to_comply_with' => $item->deadline_to_comply_with,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function aiThirdLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('ai_third_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'architect_instruction_id' => $item->architect_instruction_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'compliance_date' => $item->compliance_date,
                      'compliance_status' => $item->compliance_status,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'reason' => $item->reason,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function architectInstructionEngineerInstruction()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('architect_instruction_engineer_instruction')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'architect_instruction_id' => $item->architect_instruction_id,
                      'engineer_instruction_id' => $item->engineer_instruction_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function apportionmentTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('apportionment_types')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function architectInstructionInterimClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('architect_instruction_interim_claims')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'architect_instruction_id' => $item->architect_instruction_id,
                      'interim_claim_id' => $item->interim_claim_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'subject' => $item->subject,
                      'letter_to_contractor' => $item->letter_to_contractor,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function architectInstructionMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('architect_instruction_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'architect_instruction_id' => $item->architect_instruction_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'reason' => $item->reason,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function attachedClauseItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('attached_clause_items')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'attachable_id' => $item->attachable_id,
                      'origin_id' => $item->origin_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'priority' => $item->priority,
                      'attachable_type' => $item->attachable_type,
                      'no' => $item->no,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function calendarSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('calendar_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'country_id' => $item->country_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function claimCertificateEmailLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('claim_certificate_email_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'claim_certificate_id' => $item->claim_certificate_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function cidbGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('cidb_grades')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'parent_id' => $item->parent_id,
                      'disabled' => $item->disabled,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'grade' => $item->grade,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function assignCompaniesLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('assign_companies_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function assignCompanyInDetailLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('assign_company_in_detail_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'assign_company_log_id' => $item->assign_company_log_id,
                      'contract_group_id' => $item->contract_group_id,
                      'company_id' => $item->company_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function authenticationLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('authentication_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'login_at' => $item->login_at,
                      'logout_at' => $item->logout_at,
                      'ip_address' => $item->ip_address,
                      'user_agent' => $item->user_agent,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function calendars()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('calendars')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'country_id' => $item->country_id,
                      'state_id' => $item->state_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'start_date' => $item->start_date,
                      'end_date' => $item->end_date,
                      'event_type' => $item->event_type,
                      'description' => $item->description,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function claimCertificateInvoiceInformationUpdateLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('claim_certificate_invoice_information_update_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'claim_certificate_invoice_information_id' => $item->claim_certificate_invoice_information_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function buildingInformationModellingLevels()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('building_information_modelling_levels')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function businessEntityTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('business_entity_types')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'hidden' => $item->hidden,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function cidbCodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('cidb_codes')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'parent_id' => $item->parent_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'disabled' => $item->disabled,
                      'code' => $item->code,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyCidbCode()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_cidb_code')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'cidb_code_id' => $item->cidb_code_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function claimCertificatePaymentNotificationLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('claim_certificate_payment_notification_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'claim_certificate_id' => $item->claim_certificate_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyDetailAttachmentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_detail_attachment_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'name_attachments' => $item->name_attachments,
                      'address_attachments' => $item->address_attachments,
                      'contract_group_category_attachments' => $item->contract_group_category_attachments,
                      'vendor_category_attachments' => $item->vendor_category_attachments,
                      'main_contact_attachments' => $item->main_contact_attachments,
                      'reference_number_attachments' => $item->reference_number_attachments,
                      'tax_registration_number_attachments' => $item->tax_registration_number_attachments,
                      'email_attachments' => $item->email_attachments,
                      'telephone_attachments' => $item->telephone_attachments,
                      'fax_attachments' => $item->fax_attachments,
                      'country_attachments' => $item->country_attachments,
                      'state_attachments' => $item->state_attachments,
                      'company_status_attachments' => $item->company_status_attachments,
                      'bumiputera_equity_attachments' => $item->bumiputera_equity_attachments,
                      'non_bumiputera_equity_attachments' => $item->non_bumiputera_equity_attachments,
                      'foreigner_equity_attachments' => $item->foreigner_equity_attachments,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'cidb_grade_attachments' => $item->cidb_grade_attachments,
                      'bim_level_attachments' => $item->bim_level_attachments,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function claimCertificatePayments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('claim_certificate_payments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'notification_sent' => $item->notification_sent,
                      'claim_certificate_id' => $item->claim_certificate_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'amount' => $item->amount,
                      'date' => $item->date,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'bank' => $item->bank,
                      'reference' => $item->reference,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function claimCertificatePrintLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('claim_certificate_print_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'claim_certificate_id' => $item->claim_certificate_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function clauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('clauses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'contract_id' => $item->contract_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'type' => $item->type,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyImportedUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_imported_users')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyImportedUsersLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_imported_users_log')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'user_id' => $item->user_id,
                      'created_by' => $item->created_by,
                      'import' => $item->import,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyPersonnelSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_personnel_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'has_attachments' => $item->has_attachments,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyTenderCallingTenderInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_tender_calling_tender_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'tender_calling_tender_information_id' => $item->tender_calling_tender_information_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyTenderLotInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_tender_lot_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'tender_lot_information_id' => $item->tender_lot_information_id,
                      'added_by_gcd' => $item->added_by_gcd,
                      'status' => $item->status,
                      'deleted_at' => $item->deleted_at,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyTenderRotInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_tender_rot_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'status' => $item->status,
                      'company_id' => $item->company_id,
                      'tender_rot_information_id' => $item->tender_rot_information_id,
                      'id' => $item->id,
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyTenderTenderAlternatives()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_tender_tender_alternatives')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'tender_alternative_id' => $item->tender_alternative_id,
                      'tender_amount' => $item->tender_amount,
                      'other_bill_type_amount_except_prime_cost_provisional' => $item->other_bill_type_amount_except_prime_cost_provisional,
                      'supply_of_material_amount' => $item->supply_of_material_amount,
                      'original_tender_amount' => $item->original_tender_amount,
                      'discounted_percentage' => $item->discounted_percentage,
                      'discounted_amount' => $item->discounted_amount,
                      'completion_period' => $item->completion_period,
                      'contractor_adjustment_amount' => $item->contractor_adjustment_amount,
                      'contractor_adjustment_percentage' => $item->contractor_adjustment_percentage,
                      'earnest_money' => $item->earnest_money,
                      'company_tender_id' => $item->company_tender_id,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyProject()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_project')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'company_id' => $item->company_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'contract_group_id' => $item->contract_group_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyPropertyDevelopers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_property_developers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'property_developer_id' => $item->property_developer_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyTemporaryDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_temporary_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'state_id' => $item->state_id,
                      'vendor_registration_id' => $item->vendor_registration_id,
                      'foreigner_equity' => $item->foreigner_equity,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'company_status' => $item->company_status,
                      'cidb_grade' => $item->cidb_grade,
                      'bim_level_id' => $item->bim_level_id,
                      'country_id' => $item->country_id,
                      'id' => $item->id,
                      'is_bumiputera' => $item->is_bumiputera,
                      'bumiputera_equity' => $item->bumiputera_equity,
                      'non_bumiputera_equity' => $item->non_bumiputera_equity,
                      'address' => $item->address,
                      'main_contact' => $item->main_contact,
                      'tax_registration_no' => $item->tax_registration_no,
                      'email' => $item->email,
                      'telephone_number' => $item->telephone_number,
                      'fax_number' => $item->fax_number,
                      'reference_no' => $item->reference_no,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyTender()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_tender')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'contractor_adjustment_amount' => $item->contractor_adjustment_amount,
                      'company_id' => $item->company_id,
                      'tender_id' => $item->tender_id,
                      'discounted_amount' => $item->discounted_amount,
                      'earnest_money' => $item->earnest_money,
                      'id' => $item->id,
                      'completion_period' => $item->completion_period,
                      'submitted' => $item->submitted,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'can_login' => $item->can_login,
                      'selected_contractor' => $item->selected_contractor,
                      'submitted_at' => $item->submitted_at,
                      'supply_of_material_amount' => $item->supply_of_material_amount,
                      'other_bill_type_amount_except_prime_cost_provisional' => $item->other_bill_type_amount_except_prime_cost_provisional,
                      'contractor_adjustment_percentage' => $item->contractor_adjustment_percentage,
                      'original_tender_amount' => $item->original_tender_amount,
                      'discounted_percentage' => $item->discounted_percentage,
                      'rates' => $item->rates,
                      'tender_amount' => $item->tender_amount,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyVendorCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_vendor_category')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'company_id' => $item->company_id,
                      'vendor_category_id' => $item->vendor_category_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementApprovalDocumentSectionE()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_approval_document_section_e')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_approval_document_id' => $item->consultant_management_approval_document_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_attachment_setting_id' => $item->consultant_management_attachment_setting_id,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'company_id' => $item->company_id,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_questionnaires')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'company_id' => $item->company_id,
                      'status' => $item->status,
                      'published_date' => $item->published_date,
                      'unpublished_date' => $item->unpublished_date,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementCallingRfp()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_calling_rfp')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_rfp_revision_id' => $item->consultant_management_rfp_revision_id,
                      'calling_rfp_date' => $item->calling_rfp_date,
                      'closing_rfp_date' => $item->closing_rfp_date,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'is_extend' => $item->is_extend,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementCompanyRoleLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_company_role_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'role' => $item->role,
                      'consultant_management_contract_id' => $item->consultant_management_contract_id,
                      'company_id' => $item->company_id,
                      'calling_rfp' => $item->calling_rfp,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementApprovalDocuments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_approval_documents')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'id' => $item->id,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'document_reference_no' => $item->document_reference_no,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementApprovalDocumentSectionAppendix()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_approval_document_section_appendix')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_approval_document_id' => $item->consultant_management_approval_document_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementApprovalDocumentSectionC()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_approval_document_section_c')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_approval_document_id' => $item->consultant_management_approval_document_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementApprovalDocumentSectionD()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_approval_document_section_d')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_approval_document_id' => $item->consultant_management_approval_document_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementApprovalDocumentSectionB()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_approval_document_section_b')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_by' => $item->created_by,
                      'consultant_management_approval_document_id' => $item->consultant_management_approval_document_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'project_brief' => $item->project_brief,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementApprovalDocumentVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_approval_document_verifiers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_approval_document_id' => $item->consultant_management_approval_document_id,
                      'user_id' => $item->user_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementApprovalDocumentVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_approval_document_verifier_versions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_approval_document_verifier_id' => $item->consultant_management_approval_document_verifier_id,
                      'user_id' => $item->user_id,
                      'version' => $item->version,
                      'status' => $item->status,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantQuestionnaireReplies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_questionnaire_replies')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_questionnaire_id' => $item->consultant_management_questionnaire_id,
                      'consultant_management_consultant_questionnaire_id' => $item->consultant_management_consultant_questionnaire_id,
                      'id' => $item->id,
                      'consultant_management_questionnaire_option_id' => $item->consultant_management_questionnaire_option_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'text' => $item->text,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementCallingRfpVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_calling_rfp_verifiers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_calling_rfp_id' => $item->consultant_management_calling_rfp_id,
                      'user_id' => $item->user_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementCallingRfpCompanies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_calling_rfp_companies')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_calling_rfp_id' => $item->consultant_management_calling_rfp_id,
                      'company_id' => $item->company_id,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementCallRfpVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_call_rfp_verifier_versions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_calling_rfp_verifier_id' => $item->consultant_management_calling_rfp_verifier_id,
                      'user_id' => $item->user_id,
                      'version' => $item->version,
                      'status' => $item->status,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantRfpQuestionnaireReplies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_rfp_questionnaire_replies')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_rfp_questionnaire_id' => $item->consultant_management_rfp_questionnaire_id,
                      'consultant_management_consultant_questionnaire_id' => $item->consultant_management_consultant_questionnaire_id,
                      'id' => $item->id,
                      'consultant_management_rfp_questionnaire_option_id' => $item->consultant_management_rfp_questionnaire_option_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'text' => $item->text,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantRfp()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_rfp')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_rfp_revision_id' => $item->consultant_management_rfp_revision_id,
                      'company_id' => $item->company_id,
                      'awarded' => $item->awarded,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantRfpReplyAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_rfp_reply_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_rfp_questionnaire_id' => $item->consultant_management_rfp_questionnaire_id,
                      'consultant_management_consultant_questionnaire_id' => $item->consultant_management_consultant_questionnaire_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantRfpCommonInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_rfp_common_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_consultant_rfp_id' => $item->consultant_management_consultant_rfp_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'contact_email' => $item->contact_email,
                      'name_in_loa' => $item->name_in_loa,
                      'remarks' => $item->remarks,
                      'contact_name' => $item->contact_name,
                      'contact_number' => $item->contact_number,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementLetterOfAwardClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_letter_of_award_clauses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'template_id' => $item->template_id,
                      'id' => $item->id,
                      'display_numbering' => $item->display_numbering,
                      'sequence_number' => $item->sequence_number,
                      'parent_id' => $item->parent_id,
                      'created_at' => $item->created_at,
                      'content' => $item->content,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementLetterOfAwardTemplateClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_letter_of_award_template_clauses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'template_id' => $item->template_id,
                      'id' => $item->id,
                      'display_numbering' => $item->display_numbering,
                      'sequence_number' => $item->sequence_number,
                      'parent_id' => $item->parent_id,
                      'created_at' => $item->created_at,
                      'content' => $item->content,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementContracts()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_contracts')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'subsidiary_id' => $item->subsidiary_id,
                      'country_id' => $item->country_id,
                      'state_id' => $item->state_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'reference_no' => $item->reference_no,
                      'title' => $item->title,
                      'description' => $item->description,
                      'address' => $item->address,
                      'modified_currency_code' => $item->modified_currency_code,
                      'modified_currency_name' => $item->modified_currency_name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantRfpProposedFees()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_rfp_proposed_fees')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_consultant_rfp_id' => $item->consultant_management_consultant_rfp_id,
                      'consultant_management_subsidiary_id' => $item->consultant_management_subsidiary_id,
                      'proposed_fee_percentage' => $item->proposed_fee_percentage,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'proposed_fee_amount' => $item->proposed_fee_amount,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_users')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'is_admin' => $item->is_admin,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementExcludeAttachmentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_exclude_attachment_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_attachment_setting_id' => $item->consultant_management_attachment_setting_id,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantReplyAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_reply_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_questionnaire_id' => $item->consultant_management_questionnaire_id,
                      'consultant_management_consultant_questionnaire_id' => $item->consultant_management_consultant_questionnaire_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementConsultantRfpAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_consultant_rfp_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_rfp_attachment_setting_id' => $item->consultant_management_rfp_attachment_setting_id,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'company_id' => $item->company_id,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementExcludeQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_exclude_questionnaires')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_questionnaire_id' => $item->consultant_management_questionnaire_id,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'company_id' => $item->company_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementLetterOfAwardAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_letter_of_award_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_letter_of_award_id' => $item->consultant_management_letter_of_award_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'title' => $item->title,
                      'attachment_filename' => $item->attachment_filename,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementListOfConsultants()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_list_of_consultants')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_rfp_revision_id' => $item->consultant_management_rfp_revision_id,
                      'proposed_fee' => $item->proposed_fee,
                      'calling_rfp_date' => $item->calling_rfp_date,
                      'closing_rfp_date' => $item->closing_rfp_date,
                      'updated_at' => $item->updated_at,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementLetterOfAwardTemplates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_letter_of_award_templates')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'title' => $item->title,
                      'letterhead' => $item->letterhead,
                      'signatory' => $item->signatory,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementListOfConsultantVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_list_of_consultant_verifiers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_list_of_consultant_id' => $item->consultant_management_list_of_consultant_id,
                      'user_id' => $item->user_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementProductTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_product_types')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_subsidiary_id' => $item->consultant_management_subsidiary_id,
                      'product_type_id' => $item->product_type_id,
                      'number_of_unit' => $item->number_of_unit,
                      'lot_dimension_length' => $item->lot_dimension_length,
                      'lot_dimension_width' => $item->lot_dimension_width,
                      'proposed_built_up_area' => $item->proposed_built_up_area,
                      'proposed_average_selling_price' => $item->proposed_average_selling_price,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementLoaSubsidiaryRunningNumbers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_loa_subsidiary_running_numbers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'subsidiary_id' => $item->subsidiary_id,
                      'next_running_number' => $item->next_running_number,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_questionnaires')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_contract_id' => $item->consultant_management_contract_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'required' => $item->required,
                      'with_attachment' => $item->with_attachment,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'question' => $item->question,
                      'type' => $item->type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementLetterOfAwards()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_letter_of_awards')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'running_number' => $item->running_number,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'signatory' => $item->signatory,
                      'reference_number' => $item->reference_number,
                      'letterhead' => $item->letterhead,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementOpenRfpVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_open_rfp_verifiers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_open_rfp_id' => $item->consultant_management_open_rfp_id,
                      'user_id' => $item->user_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementOpenRfpVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_open_rfp_verifier_versions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_open_rfp_verifier_id' => $item->consultant_management_open_rfp_verifier_id,
                      'user_id' => $item->user_id,
                      'version' => $item->version,
                      'status' => $item->status,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementQuestionnaireOptions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_questionnaire_options')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_questionnaire_id' => $item->consultant_management_questionnaire_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'order' => $item->order,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'text' => $item->text,
                      'value' => $item->value,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementLetterOfAwardVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_letter_of_award_verifiers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_letter_of_award_id' => $item->consultant_management_letter_of_award_id,
                      'user_id' => $item->user_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementLetterOfAwardVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_letter_of_award_verifier_versions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_letter_of_award_verifier_id' => $item->consultant_management_letter_of_award_verifier_id,
                      'user_id' => $item->user_id,
                      'version' => $item->version,
                      'status' => $item->status,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementListOfConsultantCompanies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_list_of_consultant_companies')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_list_of_consultant_id' => $item->consultant_management_list_of_consultant_id,
                      'company_id' => $item->company_id,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementLocVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_loc_verifier_versions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_list_of_consultant_verifier_id' => $item->consultant_management_list_of_consultant_verifier_id,
                      'user_id' => $item->user_id,
                      'version' => $item->version,
                      'status' => $item->status,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRecommendationOfConsultantCompanies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_recommendation_of_consultant_companies')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'company_id' => $item->company_id,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRecommendationOfConsultantVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_recommendation_of_consultant_verifiers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_recommendation_of_consultant_id' => $item->consultant_management_recommendation_of_consultant_id,
                      'user_id' => $item->user_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpResubmissionVerifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_resubmission_verifiers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_open_rfp_id' => $item->consultant_management_open_rfp_id,
                      'user_id' => $item->user_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpInterviewTokens()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_interview_tokens')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_rfp_interview_consultant_id' => $item->consultant_management_rfp_interview_consultant_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'token' => $item->token,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpInterviewConsultants()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_interview_consultants')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_rfp_interview_id' => $item->consultant_management_rfp_interview_id,
                      'company_id' => $item->company_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'interview_timestamp' => $item->interview_timestamp,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'remarks' => $item->remarks,
                      'consultant_remarks' => $item->consultant_remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_questionnaires')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'company_id' => $item->company_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'required' => $item->required,
                      'with_attachment' => $item->with_attachment,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'question' => $item->question,
                      'type' => $item->type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpInterviews()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_interviews')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'interview_date' => $item->interview_date,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'title' => $item->title,
                      'details' => $item->details,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpQuestionnaireOptions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_questionnaire_options')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_rfp_questionnaire_id' => $item->consultant_management_rfp_questionnaire_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'order' => $item->order,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'text' => $item->text,
                      'value' => $item->value,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRolesContractGroupCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_roles_contract_group_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'role' => $item->role,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpRevisions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_revisions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'revision' => $item->revision,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpAttachmentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_attachment_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'id' => $item->id,
                      'mandatory' => $item->mandatory,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'title' => $item->title,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpDocuments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_documents')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_by' => $item->created_by,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRfpResubmissionVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_rfp_resubmission_verifier_versions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_rfp_resubmission_verifier_id' => $item->consultant_management_rfp_resubmission_verifier_id,
                      'user_id' => $item->user_id,
                      'version' => $item->version,
                      'status' => $item->status,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRecommendationOfConsultants()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_recommendation_of_consultants')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'proposed_fee' => $item->proposed_fee,
                      'calling_rfp_proposed_date' => $item->calling_rfp_proposed_date,
                      'closing_rfp_proposed_date' => $item->closing_rfp_proposed_date,
                      'updated_at' => $item->updated_at,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementRocVerifierVersions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_roc_verifier_versions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_recommendation_of_consultant_verifier_id' => $item->consultant_management_recommendation_of_consultant_verifier_id,
                      'user_id' => $item->user_id,
                      'version' => $item->version,
                      'status' => $item->status,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementSectionDDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_section_d_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_approval_document_section_d_id' => $item->consultant_management_approval_document_section_d_id,
                      'company_id' => $item->company_id,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'scope_of_services' => $item->scope_of_services,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementSectionDServiceFees()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_section_d_service_fees')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_approval_document_section_d_id' => $item->consultant_management_approval_document_section_d_id,
                      'consultant_management_subsidiary_id' => $item->consultant_management_subsidiary_id,
                      'company_id' => $item->company_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'board_scale_of_fee' => $item->board_scale_of_fee,
                      'notes' => $item->notes,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractGroupCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_group_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'vendor_type' => $item->vendor_type,
                      'type' => $item->type,
                      'id' => $item->id,
                      'editable' => $item->editable,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'default_buildspace_access' => $item->default_buildspace_access,
                      'hidden' => $item->hidden,
                      'name' => $item->name,
                      'code' => $item->code,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementVendorCategoriesRfp()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_vendor_categories_rfp')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_category_id' => $item->vendor_category_id,
                      'consultant_management_contract_id' => $item->consultant_management_contract_id,
                      'cost_type' => $item->cost_type,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractGroupCategoryPrivileges()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_group_category_privileges')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'identifier' => $item->identifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_groups')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'group' => $item->group,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementSubsidiaries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_subsidiaries')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_contract_id' => $item->consultant_management_contract_id,
                      'subsidiary_id' => $item->subsidiary_id,
                      'development_type_id' => $item->development_type_id,
                      'id' => $item->id,
                      'gross_acreage' => $item->gross_acreage,
                      'project_budget' => $item->project_budget,
                      'total_construction_cost' => $item->total_construction_cost,
                      'total_landscape_cost' => $item->total_landscape_cost,
                      'cost_per_square_feet' => $item->cost_per_square_feet,
                      'planning_permission_date' => $item->planning_permission_date,
                      'building_plan_date' => $item->building_plan_date,
                      'launch_date' => $item->launch_date,
                      'position' => $item->position,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'business_case' => $item->business_case,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementSectionAppendixDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_section_appendix_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_approval_document_section_appendix_id' => $item->consultant_management_approval_document_section_appendix_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'title' => $item->title,
                      'attachment_filename' => $item->attachment_filename,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementSectionCDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_section_c_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_approval_document_section_c_id' => $item->consultant_management_approval_document_section_c_id,
                      'consultant_management_subsidiary_id' => $item->consultant_management_subsidiary_id,
                      'company_id' => $item->company_id,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementUserRoles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_user_roles')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'role' => $item->role,
                      'consultant_management_contract_id' => $item->consultant_management_contract_id,
                      'user_id' => $item->user_id,
                      'editor' => $item->editor,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementVendorCategoriesRfpAccountCode()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_vendor_categories_rfp_account_code')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_category_rfp_id' => $item->vendor_category_rfp_id,
                      'account_code_id' => $item->account_code_id,
                      'amount' => $item->amount,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractGroupContractGroupCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_group_contract_group_category')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'contract_group_id' => $item->contract_group_id,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractGroupConversation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_group_conversation')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'contract_group_id' => $item->contract_group_id,
                      'conversation_id' => $item->conversation_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'read' => $item->read,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractGroupDocumentManagementFolder()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_group_document_management_folder')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'contract_group_id' => $item->contract_group_id,
                      'document_management_folder_id' => $item->document_management_folder_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractGroupProjectUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_group_project_users')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'contract_group_id' => $item->contract_group_id,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'is_contract_group_project_owner' => $item->is_contract_group_project_owner,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractGroupTenderDocumentPermissionLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_group_tender_document_permission_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'assign_company_log_id' => $item->assign_company_log_id,
                      'contract_group_id' => $item->contract_group_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractManagementUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_management_user_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'module_identifier' => $item->module_identifier,
                      'user_id' => $item->user_id,
                      'project_id' => $item->project_id,
                      'is_editor' => $item->is_editor,
                      'is_verifier' => $item->is_verifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractLimits()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contract_limits')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'limit' => $item->limit,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractorQuestionnaireReplies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractor_questionnaire_replies')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'contractor_questionnaire_question_id' => $item->contractor_questionnaire_question_id,
                      'id' => $item->id,
                      'contractor_questionnaire_option_id' => $item->contractor_questionnaire_option_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'text' => $item->text,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractorQuestionnaireReplyAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractor_questionnaire_reply_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'contractor_questionnaire_question_id' => $item->contractor_questionnaire_question_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractorWorkSubcategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractor_work_subcategory')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'contractor_id' => $item->contractor_id,
                      'work_subcategory_id' => $item->work_subcategory_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractorRegistrationStatuses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractor_registration_statuses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractorQuestionnaires()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractor_questionnaires')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'company_id' => $item->company_id,
                      'status' => $item->status,
                      'published_date' => $item->published_date,
                      'unpublished_date' => $item->unpublished_date,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractors')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'company_id' => $item->company_id,
                      'previous_cpe_grade_id' => $item->previous_cpe_grade_id,
                      'current_cpe_grade_id' => $item->current_cpe_grade_id,
                      'registration_status_id' => $item->registration_status_id,
                      'job_limit_sign' => $item->job_limit_sign,
                      'job_limit_number' => $item->job_limit_number,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'registered_date' => $item->registered_date,
                      'cidb_category' => $item->cidb_category,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function costData()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('cost_data')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'buildspace_origin_id' => $item->buildspace_origin_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'notes' => $item->notes,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectStatuses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_statuses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'status_text' => $item->status_text,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contracts()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contracts')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'type' => $item->type,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function conversations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('conversations')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'project_id' => $item->project_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'purpose_of_issued' => $item->purpose_of_issued,
                      'deadline_to_reply' => $item->deadline_to_reply,
                      'status' => $item->status,
                      'send_by_contract_group_id' => $item->send_by_contract_group_id,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractorsCommitmentStatusLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractors_commitment_status_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'loggable_id' => $item->loggable_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'status' => $item->status,
                      'loggable_type' => $item->loggable_type,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function conversationReplyMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('conversation_reply_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'conversation_id' => $item->conversation_id,
                      'created_by' => $item->created_by,
                      'id' => $item->id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractorQuestionnaireQuestions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractor_questionnaire_questions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'contractor_questionnaire_id' => $item->contractor_questionnaire_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'required' => $item->required,
                      'with_attachment' => $item->with_attachment,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'question' => $item->question,
                      'type' => $item->type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractorQuestionnaireOptions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractor_questionnaire_options')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'contractor_questionnaire_question_id' => $item->contractor_questionnaire_question_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'order' => $item->order,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'text' => $item->text,
                      'value' => $item->value,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function dailyReport()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('daily_report')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'project_id' => $item->project_id,
                      'id' => $item->id,
                      'instruction_date' => $item->instruction_date,
                      'submitted_by' => $item->submitted_by,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'instruction' => $item->instruction,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function dashboardGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('dashboard_groups')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'type' => $item->type,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'title' => $item->title,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function directedTo()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('directed_to')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'target_id' => $item->target_id,
                      'object_id' => $item->object_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'object_type' => $item->object_type,
                      'target_type' => $item->target_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function dynamicForms()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('dynamic_forms')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'renewal_approval_required' => $item->renewal_approval_required,
                      'root_id' => $item->root_id,
                      'module_identifier' => $item->module_identifier,
                      'id' => $item->id,
                      'is_template' => $item->is_template,
                      'revision' => $item->revision,
                      'status' => $item->status,
                      'submitted_for_approval_by' => $item->submitted_for_approval_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'submission_status' => $item->submission_status,
                      'origin_id' => $item->origin_id,
                      'is_renewal_form' => $item->is_renewal_form,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eBiddingEmailReminders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('e_bidding_email_reminders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'ebidding_id' => $item->ebidding_id,
                      'status_bidding_start_time' => $item->status_bidding_start_time,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'status_preview_start_time' => $item->status_preview_start_time,
                      'subject' => $item->subject,
                      'message' => $item->message,
                      'message2' => $item->message2,
                      'subject2' => $item->subject2,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function documentManagementFolders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('document_management_folders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'root_id' => $item->root_id,
                      'parent_id' => $item->parent_id,
                      'lft' => $item->lft,
                      'rgt' => $item->rgt,
                      'depth' => $item->depth,
                      'priority' => $item->priority,
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'contract_group_id' => $item->contract_group_id,
                      'folder_type' => $item->folder_type,
                      'created_at' => $item->created_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function currentCpeGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('current_cpe_grades')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'grade' => $item->grade,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function currencySettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('currency_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'country_id' => $item->country_id,
                      'rounding_type' => $item->rounding_type,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function dailyLabourReports()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('daily_labour_reports')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'date' => $item->date,
                      'weather_id' => $item->weather_id,
                      'bill_column_setting_id' => $item->bill_column_setting_id,
                      'unit' => $item->unit,
                      'project_structure_location_code_id' => $item->project_structure_location_code_id,
                      'pre_defined_location_code_id' => $item->pre_defined_location_code_id,
                      'contractor_id' => $item->contractor_id,
                      'project_id' => $item->project_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'submitted_by' => $item->submitted_by,
                      'work_description' => $item->work_description,
                      'remark' => $item->remark,
                      'path_to_photo' => $item->path_to_photo,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function dashboardGroupsExcludedProjects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('dashboard_groups_excluded_projects')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'project_id' => $item->project_id,
                      'dashboard_group_type' => $item->dashboard_group_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function dashboardGroupsUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('dashboard_groups_users')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'user_id' => $item->user_id,
                      'dashboard_group_type' => $item->dashboard_group_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function defectCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('defect_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function defectCategoryPreDefinedLocationCode()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('defect_category_pre_defined_location_code')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'pre_defined_location_code_id' => $item->pre_defined_location_code_id,
                      'defect_category_id' => $item->defect_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function defects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('defects')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'defect_category_id' => $item->defect_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function developmentTypesProductTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('development_types_product_types')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'development_type_id' => $item->development_type_id,
                      'product_type_id' => $item->product_type_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function documentControlObjects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('document_control_objects')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'project_id' => $item->project_id,
                      'reference_number' => $item->reference_number,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'issuer_id' => $item->issuer_id,
                      'subject' => $item->subject,
                      'message_type' => $item->message_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eBiddingCommittees()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('e_bidding_committees')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'contract_group_id' => $item->contract_group_id,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'is_committee' => $item->is_committee,
                      'is_verifier' => $item->is_verifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function elementAttributes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('element_attributes')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'element_id' => $item->element_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'name' => $item->name,
                      'element_class' => $item->element_class,
                      'value' => $item->value,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function emailAnnouncementRecipients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('email_announcement_recipients')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'email_announcement_id' => $item->email_announcement_id,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function engineerInstructions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('engineer_instructions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'type' => $item->type,
                      'project_id' => $item->project_id,
                      'created_by' => $item->created_by,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'deadline_to_comply_with' => $item->deadline_to_comply_with,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'subject' => $item->subject,
                      'detailed_elaborations' => $item->detailed_elaborations,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function elements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('elements')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'has_attachments' => $item->has_attachments,
                      'parent_id' => $item->parent_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'is_other_option' => $item->is_other_option,
                      'is_key_information' => $item->is_key_information,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'label' => $item->label,
                      'instructions' => $item->instructions,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function emailNotifications()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('email_notifications')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'project_id' => $item->project_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function emailNotificationSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('email_notification_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'setting_identifier' => $item->setting_identifier,
                      'activated' => $item->activated,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'modifiable_contents' => $item->modifiable_contents,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function elementDefinitions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('element_definitions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'element_render_identifier' => $item->element_render_identifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'module_class' => $item->module_class,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function emailReminderSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('email_reminder_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_reminder_before_closing_date_value' => $item->tender_reminder_before_closing_date_value,
                      'tender_reminder_before_closing_date_unit' => $item->tender_reminder_before_closing_date_unit,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function emailSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('email_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_logo_alignment_identifier' => $item->company_logo_alignment_identifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'footer_logo_height' => $item->footer_logo_height,
                      'resize_footer_image' => $item->resize_footer_image,
                      'footer_logo_width' => $item->footer_logo_width,
                      'footer_logo_image' => $item->footer_logo_image,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function elementValues()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('element_values')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'element_id' => $item->element_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'element_class' => $item->element_class,
                      'value' => $item->value,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function elementRejections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('element_rejections')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_by' => $item->updated_by,
                      'element_id' => $item->element_id,
                      'created_by' => $item->created_by,
                      'id' => $item->id,
                      'is_amended' => $item->is_amended,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'element_class' => $item->element_class,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function emailAnnouncements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('email_announcements')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eBiddings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('e_biddings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'preview_start_time' => $item->preview_start_time,
                      'reminder_preview_start_time' => $item->reminder_preview_start_time,
                      'bidding_start_time' => $item->bidding_start_time,
                      'reminder_bidding_start_time' => $item->reminder_bidding_start_time,
                      'duration_hours' => $item->duration_hours,
                      'duration_minutes' => $item->duration_minutes,
                      'start_overtime' => $item->start_overtime,
                      'overtime_period' => $item->overtime_period,
                      'set_budget' => $item->set_budget,
                      'budget' => $item->budget,
                      'bid_decrement_percent' => $item->bid_decrement_percent,
                      'decrement_percent' => $item->decrement_percent,
                      'bid_decrement_value' => $item->bid_decrement_value,
                      'decrement_value' => $item->decrement_value,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'duration_extended' => $item->duration_extended,
                      'lowest_tender_amount' => $item->lowest_tender_amount,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function extensionOfTimes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('extension_of_times')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'architect_instruction_id' => $item->architect_instruction_id,
                      'created_by' => $item->created_by,
                      'commencement_date_of_event' => $item->commencement_date_of_event,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'initial_estimate_of_eot' => $item->initial_estimate_of_eot,
                      'days_claimed' => $item->days_claimed,
                      'days_granted' => $item->days_granted,
                      'status' => $item->status,
                      'subject' => $item->subject,
                      'detailed_elaborations' => $item->detailed_elaborations,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eotFourthLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('eot_fourth_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'extension_of_time_id' => $item->extension_of_time_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'grant_different_days' => $item->grant_different_days,
                      'decision' => $item->decision,
                      'type' => $item->type,
                      'locked' => $item->locked,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eotSecondLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('eot_second_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'extension_of_time_id' => $item->extension_of_time_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'requested_new_deadline' => $item->requested_new_deadline,
                      'grant_different_deadline' => $item->grant_different_deadline,
                      'decision' => $item->decision,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eotThirdLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('eot_third_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'extension_of_time_id' => $item->extension_of_time_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deadline_to_comply_with' => $item->deadline_to_comply_with,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function externalApplicationAttributes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('external_application_attributes')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'client_module_id' => $item->client_module_id,
                      'internal_attribute' => $item->internal_attribute,
                      'id' => $item->id,
                      'is_identifier' => $item->is_identifier,
                      'created_at' => $item->created_at,
                      'external_attribute' => $item->external_attribute,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function expressionOfInterestTokens()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('expression_of_interest_tokens')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'tenderstageable_id' => $item->tenderstageable_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'company_id' => $item->company_id,
                      'tenderstageable_type' => $item->tenderstageable_type,
                      'token' => $item->token,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eotFirstLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('eot_first_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'extension_of_time_id' => $item->extension_of_time_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'decision' => $item->decision,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'details' => $item->details,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function externalAppAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('external_app_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'reference_id' => $item->reference_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'filename' => $item->filename,
                      'remarks' => $item->remarks,
                      'file_path' => $item->file_path,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function externalAppCompanyAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('external_app_company_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'reference_id' => $item->reference_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'filename' => $item->filename,
                      'document_type' => $item->document_type,
                      'file_path' => $item->file_path,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eotContractorConfirmDelays()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('eot_contractor_confirm_delays')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'extension_of_time_id' => $item->extension_of_time_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'date_on_which_delay_is_over' => $item->date_on_which_delay_is_over,
                      'deadline_to_submit_final_eot_claim' => $item->deadline_to_submit_final_eot_claim,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function extensionOfTimeClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('extension_of_time_claims')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'extension_of_time_id' => $item->extension_of_time_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'days_claimed' => $item->days_claimed,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function externalApplicationClientOutboundLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('external_application_client_outbound_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'client_id' => $item->client_id,
                      'data' => $item->data,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'module' => $item->module,
                      'response_contents' => $item->response_contents,
                      'status_code' => $item->status_code,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function externalApplicationIdentifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('external_application_identifiers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'client_module_id' => $item->client_module_id,
                      'internal_identifier' => $item->internal_identifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'class_name' => $item->class_name,
                      'external_identifier' => $item->external_identifier,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function externalApplicationClientOutboundAuthorizations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('external_application_client_outbound_authorizations')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'client_id' => $item->client_id,
                      'type' => $item->type,
                      'options' => $item->options,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'url' => $item->url,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function fileNodePermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('file_node_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'file_node_id' => $item->file_node_id,
                      'is_editor' => $item->is_editor,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function failedJobs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('failed_jobs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'failed_at' => $item->failed_at,
                      'connection' => $item->connection,
                      'queue' => $item->queue,
                      'payload' => $item->payload,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function financeUserSubsidiaries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('finance_user_subsidiaries')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'user_id' => $item->user_id,
                      'subsidiary_id' => $item->subsidiary_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formOfTenderClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_of_tender_clauses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'form_of_tender_id' => $item->form_of_tender_id,
                      'id' => $item->id,
                      'parent_id' => $item->parent_id,
                      'sequence_number' => $item->sequence_number,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'is_editable' => $item->is_editable,
                      'clause' => $item->clause,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function externalApplicationClients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('external_application_clients')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'created_by' => $item->created_by,
                      'name' => $item->name,
                      'token' => $item->token,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function fileNodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('file_nodes')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'deleted_at' => $item->deleted_at,
                      'parent_id' => $item->parent_id,
                      'lft' => $item->lft,
                      'rgt' => $item->rgt,
                      'depth' => $item->depth,
                      'root_id' => $item->root_id,
                      'priority' => $item->priority,
                      'type' => $item->type,
                      'version' => $item->version,
                      'is_latest_version' => $item->is_latest_version,
                      'origin_id' => $item->origin_id,
                      'upload_id' => $item->upload_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'name' => $item->name,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formColumns()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_columns')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'dynamic_form_id' => $item->dynamic_form_id,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formElementMappings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_element_mappings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'form_column_section_id' => $item->form_column_section_id,
                      'element_id' => $item->element_id,
                      'id' => $item->id,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'element_class' => $item->element_class,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formObjectMappings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_object_mappings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_by' => $item->updated_by,
                      'object_id' => $item->object_id,
                      'id' => $item->id,
                      'dynamic_form_id' => $item->dynamic_form_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'created_by' => $item->created_by,
                      'object_class' => $item->object_class,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formOfTenders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_of_tenders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'is_template' => $item->is_template,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formOfTenderAddresses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_of_tender_addresses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'form_of_tender_id' => $item->form_of_tender_id,
                      'address' => $item->address,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formOfTenderLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_of_tender_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'form_of_tender_id' => $item->form_of_tender_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formOfTenderTenderAlternatives()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_of_tender_tender_alternatives')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'form_of_tender_id' => $item->form_of_tender_id,
                      'show' => $item->show,
                      'id' => $item->id,
                      'tender_alternative_class_name' => $item->tender_alternative_class_name,
                      'custom_description' => $item->custom_description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function forumPosts()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('forum_posts')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'thread_id' => $item->thread_id,
                      'parent_id' => $item->parent_id,
                      'original_post_id' => $item->original_post_id,
                      'deleted_at' => $item->deleted_at,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'content' => $item->content,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function forumPostsReadLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('forum_posts_read_log')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'post_id' => $item->post_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function generalSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('general_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'view_own_created_subsidiary' => $item->view_own_created_subsidiary,
                      'view_tenders' => $item->view_tenders,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'enable_e_bidding' => $item->enable_e_bidding,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function indonesiaCivilContractContractualClaimResponses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('indonesia_civil_contract_contractual_claim_responses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'proposed_value' => $item->proposed_value,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'object_id' => $item->object_id,
                      'sequence' => $item->sequence,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'content' => $item->content,
                      'object_type' => $item->object_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formOfTenderHeaders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_of_tender_headers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'form_of_tender_id' => $item->form_of_tender_id,
                      'header_text' => $item->header_text,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formOfTenderPrintSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_of_tender_print_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'form_of_tender_id' => $item->form_of_tender_id,
                      'id' => $item->id,
                      'margin_bottom' => $item->margin_bottom,
                      'margin_left' => $item->margin_left,
                      'margin_right' => $item->margin_right,
                      'include_header_line' => $item->include_header_line,
                      'header_spacing' => $item->header_spacing,
                      'margin_top' => $item->margin_top,
                      'footer_font_size' => $item->footer_font_size,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'font_size' => $item->font_size,
                      'title_text' => $item->title_text,
                      'footer_text' => $item->footer_text,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function forumThreads()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('forum_threads')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'type' => $item->type,
                      'project_id' => $item->project_id,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                      'title' => $item->title,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function forumThreadPrivacyLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('forum_thread_privacy_log')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'thread_id' => $item->thread_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'type' => $item->type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function forumThreadUser()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('forum_thread_user')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'thread_id' => $item->thread_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function icInfoGrossValuesAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('ic_info_gross_values_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'interim_claim_information_id' => $item->interim_claim_information_id,
                      'upload_id' => $item->upload_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function icInfoNettAdditionOmissionAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('ic_info_nett_addition_omission_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'interim_claim_information_id' => $item->interim_claim_information_id,
                      'upload_id' => $item->upload_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function indonesiaCivilContractArchitectInstructions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('indonesia_civil_contract_architect_instructions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deadline_to_comply' => $item->deadline_to_comply,
                      'status' => $item->status,
                      'reference' => $item->reference,
                      'instruction' => $item->instruction,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function indonesiaCivilContractAiRfi()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('indonesia_civil_contract_ai_rfi')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'indonesia_civil_contract_architect_instruction_id' => $item->indonesia_civil_contract_architect_instruction_id,
                      'document_control_object_id' => $item->document_control_object_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eBiddingRankings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('e_bidding_rankings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'e_bidding_id' => $item->e_bidding_id,
                      'company_id' => $item->company_id,
                      'bid_amount' => $item->bid_amount,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function indonesiaCivilContractEwLe()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('indonesia_civil_contract_ew_le')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'indonesia_civil_contract_ew_id' => $item->indonesia_civil_contract_ew_id,
                      'indonesia_civil_contract_le_id' => $item->indonesia_civil_contract_le_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function indonesiaCivilContractInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('indonesia_civil_contract_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'commencement_date' => $item->commencement_date,
                      'completion_date' => $item->completion_date,
                      'contract_sum' => $item->contract_sum,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'pre_defined_location_code_id' => $item->pre_defined_location_code_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionResults()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_results')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'inspection_id' => $item->inspection_id,
                      'inspection_role_id' => $item->inspection_role_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'submitted_by' => $item->submitted_by,
                      'submitted_at' => $item->submitted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionSubmitters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_submitters')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'inspection_group_id' => $item->inspection_group_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionLists()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_lists')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function indonesiaCivilContractExtensionsOfTime()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('indonesia_civil_contract_extensions_of_time')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'indonesia_civil_contract_ai_id' => $item->indonesia_civil_contract_ai_id,
                      'days' => $item->days,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'status' => $item->status,
                      'reference' => $item->reference,
                      'subject' => $item->subject,
                      'details' => $item->details,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function indonesiaCivilContractLossAndExpenses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('indonesia_civil_contract_loss_and_expenses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'indonesia_civil_contract_ai_id' => $item->indonesia_civil_contract_ai_id,
                      'claim_amount' => $item->claim_amount,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'status' => $item->status,
                      'reference' => $item->reference,
                      'subject' => $item->subject,
                      'details' => $item->details,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_groups')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionGroupInspectionListCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_group_inspection_list_category')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'inspection_group_id' => $item->inspection_group_id,
                      'inspection_list_category_id' => $item->inspection_list_category_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionListCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_list_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'inspection_list_id' => $item->inspection_list_id,
                      'parent_id' => $item->parent_id,
                      'lft' => $item->lft,
                      'rgt' => $item->rgt,
                      'depth' => $item->depth,
                      'id' => $item->id,
                      'type' => $item->type,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionGroupUsers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_group_users')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'inspection_role_id' => $item->inspection_role_id,
                      'inspection_group_id' => $item->inspection_group_id,
                      'user_id' => $item->user_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionRoles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_roles')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'can_request_inspection' => $item->can_request_inspection,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionListItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_list_items')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'inspection_list_category_id' => $item->inspection_list_category_id,
                      'parent_id' => $item->parent_id,
                      'lft' => $item->lft,
                      'rgt' => $item->rgt,
                      'depth' => $item->depth,
                      'id' => $item->id,
                      'priority' => $item->priority,
                      'type' => $item->type,
                      'created_at' => $item->created_at,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionItemResults()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_item_results')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'progress_status' => $item->progress_status,
                      'inspection_result_id' => $item->inspection_result_id,
                      'inspection_list_item_id' => $item->inspection_list_item_id,
                      'id' => $item->id,
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionListCategoryAdditionalFields()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_list_category_additional_fields')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'inspection_list_category_id' => $item->inspection_list_category_id,
                      'id' => $item->id,
                      'priority' => $item->priority,
                      'value' => $item->value,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspectionVerifierTemplate()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspection_verifier_template')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'inspection_group_id' => $item->inspection_group_id,
                      'user_id' => $item->user_id,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function letterOfAwardClauseComments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('letter_of_award_clause_comments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'clause_id' => $item->clause_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'comments' => $item->comments,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function inspections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('inspections')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'request_for_inspection_id' => $item->request_for_inspection_id,
                      'revision' => $item->revision,
                      'id' => $item->id,
                      'ready_for_inspection_date' => $item->ready_for_inspection_date,
                      'status' => $item->status,
                      'decision' => $item->decision,
                      'created_at' => $item->created_at,
                      'comments' => $item->comments,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function interimClaimInformations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('interim_claim_informations')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'interim_claim_id' => $item->interim_claim_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'date' => $item->date,
                      'nett_addition_omission' => $item->nett_addition_omission,
                      'date_of_certificate' => $item->date_of_certificate,
                      'net_amount_of_payment_certified' => $item->net_amount_of_payment_certified,
                      'gross_values_of_works' => $item->gross_values_of_works,
                      'type' => $item->type,
                      'reference' => $item->reference,
                      'amount_in_word' => $item->amount_in_word,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function letterOfAwardClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('letter_of_award_clauses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'letter_of_award_id' => $item->letter_of_award_id,
                      'id' => $item->id,
                      'display_numbering' => $item->display_numbering,
                      'sequence_number' => $item->sequence_number,
                      'parent_id' => $item->parent_id,
                      'created_at' => $item->created_at,
                      'contents' => $item->contents,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function letterOfAwardContractDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('letter_of_award_contract_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'letter_of_award_id' => $item->letter_of_award_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'contents' => $item->contents,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function letterOfAwardLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('letter_of_award_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'letter_of_award_id' => $item->letter_of_award_id,
                      'type_identifier' => $item->type_identifier,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function labours()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('labours')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function instructionsToContractors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('instructions_to_contractors')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'project_id' => $item->project_id,
                      'id' => $item->id,
                      'instruction_date' => $item->instruction_date,
                      'submitted_by' => $item->submitted_by,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'instruction' => $item->instruction,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function letterOfAwardPrintSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('letter_of_award_print_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'letter_of_award_id' => $item->letter_of_award_id,
                      'header_font_size' => $item->header_font_size,
                      'clause_font_size' => $item->clause_font_size,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'margin_top' => $item->margin_top,
                      'margin_bottom' => $item->margin_bottom,
                      'margin_left' => $item->margin_left,
                      'margin_right' => $item->margin_right,
                      'header_spacing' => $item->header_spacing,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function letterOfAwardSignatories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('letter_of_award_signatories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'letter_of_award_id' => $item->letter_of_award_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'contents' => $item->contents,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function languages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('languages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                      'code' => $item->code,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function licenses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('licenses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'license_key' => $item->license_key,
                      'decryption_key' => $item->decryption_key,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function lossOrAndExpenses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('loss_or_and_expenses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'architect_instruction_id' => $item->architect_instruction_id,
                      'created_by' => $item->created_by,
                      'commencement_date_of_event' => $item->commencement_date_of_event,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'initial_estimate_of_claim' => $item->initial_estimate_of_claim,
                      'amount_claimed' => $item->amount_claimed,
                      'amount_granted' => $item->amount_granted,
                      'status' => $item->status,
                      'subject' => $item->subject,
                      'detailed_elaborations' => $item->detailed_elaborations,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function loeFourthLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('loe_fourth_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'loss_or_and_expense_id' => $item->loss_or_and_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'grant_different_amount' => $item->grant_different_amount,
                      'decision' => $item->decision,
                      'type' => $item->type,
                      'locked' => $item->locked,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function loeSecondLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('loe_second_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'loss_or_and_expense_id' => $item->loss_or_and_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'requested_new_deadline' => $item->requested_new_deadline,
                      'grant_different_deadline' => $item->grant_different_deadline,
                      'decision' => $item->decision,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function loeThirdLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('loe_third_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'loss_or_and_expense_id' => $item->loss_or_and_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deadline_to_comply_with' => $item->deadline_to_comply_with,
                      'type' => $item->type,
                      'subject' => $item->subject,                              'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function lossOrAndExpenseClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('loss_or_and_expense_claims')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'loss_or_and_expense_id' => $item->loss_or_and_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'final_claim_amount' => $item->final_claim_amount,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function loginRequestFormSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('login_request_form_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'include_instructions' => $item->include_instructions,
                      'id' => $item->id,
                      'include_disclaimer' => $item->include_disclaimer,
                      'disclaimer' => $item->disclaimer,
                      'instructions' => $item->instructions,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function lossOrAndExpenseInterimClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('loss_or_and_expense_interim_claims')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'loss_or_and_expense_id' => $item->loss_or_and_expense_id,
                      'interim_claim_id' => $item->interim_claim_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function loeFirstLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('loe_first_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'loss_or_and_expense_id' => $item->loss_or_and_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'decision' => $item->decision,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'details' => $item->details,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function letterOfAwards()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('letter_of_awards')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'is_template' => $item->is_template,
                      'status' => $item->status,
                      'submitted_for_approval_by' => $item->submitted_for_approval_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function letterOfAwardUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('letter_of_award_user_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'module_identifier' => $item->module_identifier,
                      'is_editor' => $item->is_editor,
                      'added_by' => $item->added_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function loeContractorConfirmDelays()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('loe_contractor_confirm_delays')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'loss_or_and_expense_id' => $item->loss_or_and_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'date_on_which_delay_is_over' => $item->date_on_which_delay_is_over,
                      'deadline_to_submit_final_claim' => $item->deadline_to_submit_final_claim,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function machinery()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('machinery')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eBiddingBids()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('e_bidding_bids')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'e_bidding_id' => $item->e_bidding_id,
                      'company_id' => $item->company_id,
                      'duration_extended' => $item->duration_extended,
                      'decrement_percent' => $item->decrement_percent,
                      'decrement_value' => $item->decrement_value,
                      'decrement_amount' => $item->decrement_amount,
                      'bid_amount' => $item->bid_amount,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'bid_type' => $item->bid_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function migrations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('migrations')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'batch' => $item->batch,
                      'migration' => $item->migration,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function myCompanyProfiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('my_company_profiles')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                      'company_logo_path' => $item->company_logo_path,
                      'company_logo_filename' => $item->company_logo_filename,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function notificationGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('notification_groups')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function objectForumThreads()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('object_forum_threads')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'thread_id' => $item->thread_id,
                      'object_id' => $item->object_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'object_type' => $item->object_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function objectFields()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('object_fields')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'object_id' => $item->object_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'object_type' => $item->object_type,
                      'field' => $item->field,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncCompanies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_companies')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'company_id' => $item->company_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncDefectCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_defect_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'defect_category_id' => $item->defect_category_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncDefectCategoryTrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_defect_category_trades')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'defect_category_trade_id' => $item->defect_category_trade_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncDefects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_defects')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'defect_id' => $item->defect_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncProjectLabourRateContractors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_project_labour_rate_contractors')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'project_labour_rate_contractor_id' => $item->project_labour_rate_contractor_id,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncProjectLabourRateTrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_project_labour_rate_trades')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'project_labour_rate_trade_id' => $item->project_labour_rate_trade_id,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncProjectLabourRates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_project_labour_rates')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'project_labour_rate_id' => $item->project_labour_rate_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncProjectStructureLocationCodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_project_structure_location_codes')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'project_structure_location_code_id' => $item->project_structure_location_code_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncProjects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_projects')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncSiteManagementDefects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_site_management_defects')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'site_management_defect_id' => $item->site_management_defect_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncTrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_trades')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'trade_id' => $item->trade_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function mobileSyncUploads()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('mobile_sync_uploads')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'upload_id' => $item->upload_id,
                      'user_id' => $item->user_id,
                      'synced' => $item->synced,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'device_id' => $item->device_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function modulePermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('module_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'module_identifier' => $item->module_identifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'is_editor' => $item->is_editor,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function moduleUploadedFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('module_uploaded_files')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'type' => $item->type,
                      'upload_id' => $item->upload_id,
                      'uploadable_id' => $item->uploadable_id,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'uploadable_type' => $item->uploadable_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function notificationCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('notification_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                      'text' => $item->text,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function notifications()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('notifications')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'from_id' => $item->from_id,
                      'category_id' => $item->category_id,
                      'read' => $item->read,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'to_id' => $item->to_id,
                      'from_type' => $item->from_type,
                      'url' => $item->url,
                      'to_type' => $item->to_type,
                      'extra' => $item->extra,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function objectLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('object_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'object_id' => $item->object_id,
                      'id' => $item->id,
                      'module_identifier' => $item->module_identifier,
                      'action_identifier' => $item->action_identifier,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'object_class' => $item->object_class,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderBanners()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_banners')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'display_order' => $item->display_order,
                      'start_time' => $item->start_time,
                      'end_time' => $item->end_time,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'image' => $item->image,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderAwardRecommendationTenderAnalysisTableEditLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_award_recommendation_tender_analysis_table_edit_log')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'tender_id' => $item->tender_id,
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'type' => $item->type,
                      'table_name' => $item->table_name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function eBiddingEmailReminderRecipients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('e_bidding_email_reminder_recipients')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'email_reminder_id' => $item->email_reminder_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'role' => $item->role,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderAwardRecommendationTenderSummary()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_award_recommendation_tender_summary')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'consultant_estimate' => $item->consultant_estimate,
                      'budget' => $item->budget,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderPageInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_page_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'open_tender_status' => $item->open_tender_status,
                      'submitted_for_approval_by' => $item->submitted_for_approval_by,
                      'status' => $item->status,
                      'project_id' => $item->project_id,
                      'open_tender_date_to' => $item->open_tender_date_to,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'tender_id' => $item->tender_id,
                      'open_tender_date_from' => $item->open_tender_date_from,
                      'special_permission' => $item->special_permission,
                      'local_company_only' => $item->local_company_only,
                      'closing_date' => $item->closing_date,
                      'open_tender_type' => $item->open_tender_type,
                      'open_tender_number' => $item->open_tender_number,
                      'open_tender_price' => $item->open_tender_price,
                      'deliver_address' => $item->deliver_address,
                      'briefing_time' => $item->briefing_time,
                      'briefing_address' => $item->briefing_address,
                      'calling_date' => $item->calling_date,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderPersonInCharges()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_person_in_charges')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'phone_number' => $item->phone_number,
                      'department' => $item->department,
                      'name' => $item->name,
                      'email' => $item->email,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderTenderDocuments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_tender_documents')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderAwardRecommendationBillDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_award_recommendation_bill_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'buildspace_bill_id' => $item->buildspace_bill_id,
                      'consultant_pte' => $item->consultant_pte,
                      'budget' => $item->budget,
                      'bill_amount' => $item->bill_amount,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function objectTags()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('object_tags')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tag_id' => $item->tag_id,
                      'object_id' => $item->object_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'object_class' => $item->object_class,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderAnnouncements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_announcements')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'date' => $item->date,
                      'tender_id' => $item->tender_id,
                      'created_by' => $item->created_by,
                      'id' => $item->id,
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderIndustryCodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_industry_codes')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'created_by' => $item->created_by,
                      'cidb_code_id' => $item->cidb_code_id,
                      'cidb_grade_id' => $item->cidb_grade_id,
                      'vendor_category_id' => $item->vendor_category_id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderNews()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_news')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'subsidiary_id' => $item->subsidiary_id,
                      'start_time' => $item->start_time,
                      'end_time' => $item->end_time,
                      'created_by' => $item->created_by,
                      'description' => $item->description,
                      'status' => $item->status,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderAwardRecommendationFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_award_recommendation_files')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'cabinet_file_id' => $item->cabinet_file_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'filename' => $item->filename,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderAwardRecommendation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_award_recommendation')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'tender_id' => $item->tender_id,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'submitted_for_verification_by' => $item->submitted_for_verification_by,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'report_contents' => $item->report_contents,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderVerifierLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_verifier_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'user_id' => $item->user_id,
                      'type' => $item->type,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function orderItemProjectTenders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('order_item_project_tenders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'order_item_id' => $item->order_item_id,
                      'project_id' => $item->project_id,
                      'tender_id' => $item->tender_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function orderItemVendorRegPayments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('order_item_vendor_reg_payments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'order_item_id' => $item->order_item_id,
                      'vendor_registration_payment_id' => $item->vendor_registration_payment_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function orderItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('order_items')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'quantity' => $item->quantity,
                      'order_sub_id' => $item->order_sub_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'total' => $item->total,
                      'created_at' => $item->created_at,
                      'type' => $item->type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function orderPayments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('order_payments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'order_id' => $item->order_id,
                      'total' => $item->total,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'description' => $item->description,
                      'status' => $item->status,
                      'payment_gateway' => $item->payment_gateway,
                      'transaction_id' => $item->transaction_id,
                      'reference_id' => $item->reference_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function orderSubs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('order_subs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'company_id' => $item->company_id,
                      'order_id' => $item->order_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'total' => $item->total,
                      'created_at' => $item->created_at,
                      'reference_id' => $item->reference_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function orders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('orders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'company_id' => $item->company_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'user_id' => $item->user_id,
                      'id' => $item->id,
                      'reference_id' => $item->reference_id,
                      'origin' => $item->origin,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function passwordReminders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('password_reminders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_at' => $item->created_at,
                      'email' => $item->email,
                      'token' => $item->token,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function paymentGatewayResults()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('payment_gateway_results')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'verified' => $item->verified,
                      'is_ipn' => $item->is_ipn,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'info' => $item->info,
                      'data' => $item->data,
                      'payment_gateway' => $item->payment_gateway,
                      'transaction_id' => $item->transaction_id,
                      'reference_id' => $item->reference_id,
                      'status' => $item->status,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function paymentGatewaySettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('payment_gateway_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'is_sandbox' => $item->is_sandbox,
                      'is_active' => $item->is_active,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'key1' => $item->key1,
                      'payment_gateway' => $item->payment_gateway,
                      'key2' => $item->key2,
                      'button_image_url' => $item->button_image_url,
                      'merchant_id' => $item->merchant_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function processorDeleteCompanyLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('processor_delete_company_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'name' => $item->name,
                      'reference_no' => $item->reference_no,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function procurementMethods()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('procurement_methods')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderTenderRequirements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_tender_requirements')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function pam2006ProjectDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('pam_2006_project_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'commencement_date' => $item->commencement_date,
                      'completion_date' => $item->completion_date,
                      'contract_sum' => $item->contract_sum,
                      'liquidate_damages' => $item->liquidate_damages,
                      'amount_performance_bond' => $item->amount_performance_bond,
                      'interim_claim_interval' => $item->interim_claim_interval,
                      'period_of_honouring_certificate' => $item->period_of_honouring_certificate,
                      'min_days_to_comply_with_ai' => $item->min_days_to_comply_with_ai,
                      'deadline_submitting_notice_of_intention_claim_eot' => $item->deadline_submitting_notice_of_intention_claim_eot,
                      'deadline_submitting_final_claim_eot' => $item->deadline_submitting_final_claim_eot,
                      'deadline_architect_request_info_from_contractor_eot_claim' => $item->deadline_architect_request_info_from_contractor_eot_claim,
                      'deadline_architect_decide_on_contractor_eot_claim' => $item->deadline_architect_decide_on_contractor_eot_claim,
                      'deadline_submitting_note_of_intention_claim_l_and_e' => $item->deadline_submitting_note_of_intention_claim_l_and_e,
                      'deadline_submitting_final_claim_l_and_e' => $item->deadline_submitting_final_claim_l_and_e,
                      'deadline_submitting_note_of_intention_claim_ae' => $item->deadline_submitting_note_of_intention_claim_ae,
                      'deadline_submitting_final_claim_ae' => $item->deadline_submitting_final_claim_ae,
                      'percentage_of_certified_value_retained' => $item->percentage_of_certified_value_retained,
                      'limit_retention_fund' => $item->limit_retention_fund,
                      'percentage_value_of_materials_and_goods_included_in_certificate' => $item->percentage_value_of_materials_and_goods_included_in_certificate,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'period_of_architect_issue_interim_certificate' => $item->period_of_architect_issue_interim_certificate,
                      'pre_defined_location_code_id' => $item->pre_defined_location_code_id,
                      'cpc_date' => $item->cpc_date,
                      'extension_of_time_date' => $item->extension_of_time_date,
                      'defect_liability_period' => $item->defect_liability_period,
                      'defect_liability_period_unit' => $item->defect_liability_period_unit,
                      'certificate_of_making_good_defect_date' => $item->certificate_of_making_good_defect_date,
                      'cnc_date' => $item->cnc_date,
                      'performance_bond_validity_date' => $item->performance_bond_validity_date,
                      'insurance_policy_coverage_date' => $item->insurance_policy_coverage_date,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function paymentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('payment_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'is_user_selectable' => $item->is_user_selectable,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'name' => $item->name,
                      'account_number' => $item->account_number,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectModulePermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_module_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,                              'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'module_identifier' => $item->module_identifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportChartPlots()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_chart_plots')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_report_chart_id' => $item->project_report_chart_id,
                      'category_column_id' => $item->category_column_id,
                      'value_column_id' => $item->value_column_id,
                      'plot_type' => $item->plot_type,
                      'data_grouping' => $item->data_grouping,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'is_accumulated' => $item->is_accumulated,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportCharts()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_charts')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_report_type_mapping_id' => $item->project_report_type_mapping_id,
                      'chart_type' => $item->chart_type,
                      'is_locked' => $item->is_locked,
                      'order' => $item->order,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'is_published' => $item->is_published,
                      'title' => $item->title,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportTypeMappings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_type_mappings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_report_type_id' => $item->project_report_type_id,
                      'project_type' => $item->project_type,
                      'project_report_id' => $item->project_report_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'latest_rev' => $item->latest_rev,
                      'is_locked' => $item->is_locked,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportNotificationContents()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_notification_contents')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_report_notification_id' => $item->project_report_notification_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'subject' => $item->subject,
                      'body' => $item->body,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportNotificationPeriods()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_notification_periods')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_report_notification_id' => $item->project_report_notification_id,
                      'period_value' => $item->period_value,
                      'period_type' => $item->period_type,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportNotificationRecipients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_notification_recipients')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_report_notification_id' => $item->project_report_notification_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportNotifications()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_notifications')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'project_id' => $item->project_id,
                      'project_report_type_mapping_id' => $item->project_report_type_mapping_id,
                      'category_column_id' => $item->category_column_id,
                      'notification_type' => $item->notification_type,
                      'is_published' => $item->is_published,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'template_name' => $item->template_name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportColumns()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_columns')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'single_entry' => $item->single_entry,
                      'project_report_id' => $item->project_report_id,
                      'reference_id' => $item->reference_id,
                      'id' => $item->id,
                      'type' => $item->type,
                      'parent_id' => $item->parent_id,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'depth' => $item->depth,
                      'title' => $item->title,
                      'content' => $item->content,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectLabourRates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_labour_rates')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'labour_type' => $item->labour_type,
                      'normal_working_hours' => $item->normal_working_hours,
                      'normal_rate_per_hour' => $item->normal_rate_per_hour,
                      'ot_rate_per_hour' => $item->ot_rate_per_hour,
                      'project_id' => $item->project_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'submitted_by' => $item->submitted_by,
                      'pre_defined_location_code_id' => $item->pre_defined_location_code_id,
                      'contractor_id' => $item->contractor_id,
                      'mobile_sync_uuid' => $item->mobile_sync_uuid,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectContractGroupTenderDocumentPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_contract_group_tender_document_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'contract_group_id' => $item->contract_group_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectContractManagementModules()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_contract_management_modules')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'module_identifier' => $item->module_identifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectDocumentFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_document_files')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'cabinet_file_id' => $item->cabinet_file_id,
                      'project_document_folder_id' => $item->project_document_folder_id,
                      'revision' => $item->revision,
                      'parent_id' => $item->parent_id,
                      'filename' => $item->filename,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportActionLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_action_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_report_id' => $item->project_report_id,
                      'action_type' => $item->action_type,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReports()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_reports')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'root_id' => $item->root_id,
                      'origin_id' => $item->origin_id,
                      'project_report_type_mapping_id' => $item->project_report_type_mapping_id,
                      'approved_date' => $item->approved_date,
                      'revision' => $item->revision,
                      'submitted_by' => $item->submitted_by,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                      'title' => $item->title,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_types')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'is_locked' => $item->is_locked,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'title' => $item->title,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectReportUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_report_user_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'identifier' => $item->identifier,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'project_report_type_id' => $item->project_report_type_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectRoles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_roles')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'contract_group_id' => $item->contract_group_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForInformationMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_information_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'deleted_at' => $item->deleted_at,
                      'document_control_object_id' => $item->document_control_object_id,
                      'sequence_number' => $item->sequence_number,
                      'composed_by' => $item->composed_by,
                      'reply_deadline' => $item->reply_deadline,
                      'id' => $item->id,
                      'type' => $item->type,
                      'response_to' => $item->response_to,
                      'cost_impact' => $item->cost_impact,
                      'schedule_impact' => $item->schedule_impact,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'content' => $item->content,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectTrackRecordSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_track_record_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_detail_attachments' => $item->project_detail_attachments,
                      'project_quality_achievement_attachments' => $item->project_quality_achievement_attachments,
                      'project_award_recognition_attachments' => $item->project_award_recognition_attachments,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function propertyDevelopers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('property_developers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'hidden' => $item->hidden,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function purgedVendors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('purged_vendors')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'purged_at' => $item->purged_at,
                      'name' => $item->name,
                      'reference_no' => $item->reference_no,
                      'email' => $item->email,
                      'telephone_number' => $item->telephone_number,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForInspectionReplies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_inspection_replies')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'request_id' => $item->request_id,
                      'inspection_id' => $item->inspection_id,
                      'id' => $item->id,
                      'ready_date' => $item->ready_date,
                      'completed_date' => $item->completed_date,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'comments' => $item->comments,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForVariationCategoryKpiLimitUpdateLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_variation_category_kpi_limit_update_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'request_for_variation_category_id' => $item->request_for_variation_category_id,
                      'kpi_limit' => $item->kpi_limit,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForVariationContractAndContingencySum()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_variation_contract_and_contingency_sum')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'original_contract_sum' => $item->original_contract_sum,
                      'contingency_sum' => $item->contingency_sum,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'contract_sum_includes_contingency_sum' => $item->contract_sum_includes_contingency_sum,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForVariationCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_variation_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'kpi_limit' => $item->kpi_limit,
                      'name' => $item->name,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForInspections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_inspections')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'location_id' => $item->location_id,
                      'inspection_list_category_id' => $item->inspection_list_category_id,
                      'submitted_by' => $item->submitted_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projectSectionalCompletionDates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('project_sectional_completion_dates')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'sectional_completion_date' => $item->sectional_completion_date,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForInspectionInspections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_inspection_inspections')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'request_id' => $item->request_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'inspected_at' => $item->inspected_at,
                      'status' => $item->status,
                      'sequence_number' => $item->sequence_number,
                      'created_by' => $item->created_by,
                      'comments' => $item->comments,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForVariationActionLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_variation_action_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'request_for_variation_id' => $item->request_for_variation_id,
                      'user_id' => $item->user_id,
                      'permission_module_id' => $item->permission_module_id,
                      'action_type' => $item->action_type,
                      'verifier' => $item->verifier,
                      'approved' => $item->approved,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForVariationFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_variation_files')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'request_for_variation_id' => $item->request_for_variation_id,
                      'cabinet_file_id' => $item->cabinet_file_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'filename' => $item->filename,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function rejectedMaterials()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('rejected_materials')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForVariationUserPermissionGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_variation_user_permission_groups')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForVariations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_variations')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'deleted_by' => $item->deleted_by,
                      'rfv_number' => $item->rfv_number,
                      'project_id' => $item->project_id,
                      'request_for_variation_user_permission_group_id' => $item->request_for_variation_user_permission_group_id,
                      'approved_category_amount' => $item->approved_category_amount,
                      'deleted_at' => $item->deleted_at,
                      'id' => $item->id,
                      'request_for_variation_category_id' => $item->request_for_variation_category_id,
                      'nett_omission_addition' => $item->nett_omission_addition,
                      'initiated_by' => $item->initiated_by,
                      'status' => $item->status,
                      'permission_module_in_charge' => $item->permission_module_in_charge,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'accumulative_approved_rfv_amount' => $item->accumulative_approved_rfv_amount,
                      'proposed_rfv_amount' => $item->proposed_rfv_amount,
                      'submitted_by' => $item->submitted_by,
                      'ai_number' => $item->ai_number,
                      'description' => $item->description,
                      'reasons_for_variation' => $item->reasons_for_variation,
                      'time_implication' => $item->time_implication,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function scheduledMaintenance()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('scheduled_maintenance')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'is_under_maintenance' => $item->is_under_maintenance,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'start_time' => $item->start_time,
                      'end_time' => $item->end_time,
                      'message' => $item->message,
                      'image' => $item->image,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function sentTenderRemindersLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('sent_tender_reminders_log')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'sent_by' => $item->sent_by,
                      'tender_id' => $item->tender_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementMcar()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_mcar')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'contractor_id' => $item->contractor_id,
                      'project_id' => $item->project_id,
                      'site_management_defect_id' => $item->site_management_defect_id,
                      'submitted_user_id' => $item->submitted_user_id,
                      'mcar_number' => $item->mcar_number,
                      'work_description' => $item->work_description,
                      'remark' => $item->remark,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestsForInspection()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('requests_for_inspection')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'reference_number' => $item->reference_number,
                      'created_by' => $item->created_by,
                      'status' => $item->status,
                      'ready_date' => $item->ready_date,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'inspection_reference' => $item->inspection_reference,
                      'subject' => $item->subject,
                      'description' => $item->description,
                      'location' => $item->location,
                      'works' => $item->works,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementMcarFormResponses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_mcar_form_responses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'verifier_id' => $item->verifier_id,
                      'applicable' => $item->applicable,
                      'commitment_date' => $item->commitment_date,
                      'verified_at' => $item->verified_at,
                      'id' => $item->id,
                      'submitted_user_id' => $item->submitted_user_id,
                      'site_management_defect_id' => $item->site_management_defect_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'verified' => $item->verified,
                      'satisfactory' => $item->satisfactory,
                      'reinspection_date' => $item->reinspection_date,
                      'site_management_mcar_id' => $item->site_management_mcar_id,
                      'cause' => $item->cause,
                      'action' => $item->action,
                      'comment' => $item->comment,
                      'corrective' => $item->corrective,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementSiteDiaryGeneralFormResponses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_site_diary_general_form_responses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'status' => $item->status,
                      'general_date' => $item->general_date,
                      'machinery_backhoe' => $item->machinery_backhoe,
                      'machinery_crane' => $item->machinery_crane,
                      'rejected_material_id' => $item->rejected_material_id,
                      'submitted_by' => $item->submitted_by,
                      'project_id' => $item->project_id,
                      'submitted_for_approval_by' => $item->submitted_for_approval_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'general_physical_progress' => $item->general_physical_progress,
                      'general_plan_progress' => $item->general_plan_progress,
                      'weather_id' => $item->weather_id,
                      'labour_project_manager' => $item->labour_project_manager,
                      'labour_site_agent' => $item->labour_site_agent,
                      'labour_supervisor' => $item->labour_supervisor,
                      'machinery_excavator' => $item->machinery_excavator,
                      'general_time_in' => $item->general_time_in,
                      'general_time_out' => $item->general_time_out,
                      'general_day' => $item->general_day,
                      'visitor_time_in' => $item->visitor_time_in,
                      'visitor_time_out' => $item->visitor_time_out,
                      'weather_time_from' => $item->weather_time_from,
                      'weather_time_to' => $item->weather_time_to,
                      'visitor_name' => $item->visitor_name,
                      'visitor_company_name' => $item->visitor_company_name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementSiteDiaryLabours()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_site_diary_labours')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'labour_id' => $item->labour_id,
                      'site_diary_id' => $item->site_diary_id,
                      'value' => $item->value,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function requestForVariationUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('request_for_variation_user_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'module_id' => $item->module_id,
                      'is_editor' => $item->is_editor,
                      'added_by' => $item->added_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'can_view_cost_estimate' => $item->can_view_cost_estimate,
                      'can_view_vo_report' => $item->can_view_vo_report,
                      'request_for_variation_user_permission_group_id' => $item->request_for_variation_user_permission_group_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function riskRegisterMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('risk_register_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'document_control_object_id' => $item->document_control_object_id,
                      'sequence_number' => $item->sequence_number,
                      'composed_by' => $item->composed_by,
                      'reply_deadline' => $item->reply_deadline,
                      'detectability' => $item->detectability,
                      'importance' => $item->importance,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                      'type' => $item->type,
                      'response_to' => $item->response_to,
                      'probability' => $item->probability,
                      'status' => $item->status,
                      'impact' => $item->impact,
                      'content' => $item->content,
                      'trigger_event' => $item->trigger_event,
                      'risk_response' => $item->risk_response,
                      'contingency_plan' => $item->contingency_plan,
                      'category' => $item->category,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementDefectBackchargeDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_defect_backcharge_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'machinery' => $item->machinery,
                      'material' => $item->material,
                      'labour' => $item->labour,
                      'total' => $item->total,
                      'user_id' => $item->user_id,
                      'site_management_defect_id' => $item->site_management_defect_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'status_id' => $item->status_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementDefectFormResponses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_defect_form_responses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'response_identifier' => $item->response_identifier,
                      'site_management_defect_id' => $item->site_management_defect_id,
                      'user_id' => $item->user_id,
                      'remark' => $item->remark,
                      'path_to_photo' => $item->path_to_photo,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementSiteDiaryWeathers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_site_diary_weathers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'site_diary_id' => $item->site_diary_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'weather_id' => $item->weather_id,
                      'weather_time_from' => $item->weather_time_from,
                      'weather_time_to' => $item->weather_time_to,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_user_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'module_identifier' => $item->module_identifier,
                      'user_id' => $item->user_id,
                      'project_id' => $item->project_id,
                      'site' => $item->site,
                      'qa_qc_client' => $item->qa_qc_client,
                      'pm' => $item->pm,
                      'qs' => $item->qs,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'is_editor' => $item->is_editor,
                      'is_viewer' => $item->is_viewer,
                      'is_rate_editor' => $item->is_rate_editor,
                      'is_verifier' => $item->is_verifier,
                      'is_submitter' => $item->is_submitter,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function subsidiaries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('subsidiaries')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'company_id' => $item->company_id,
                      'updated_at' => $item->updated_at,
                      'parent_id' => $item->parent_id,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'name' => $item->name,
                      'identifier' => $item->identifier,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function subsidiaryApportionmentRecords()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('subsidiary_apportionment_records')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'subsidiary_id' => $item->subsidiary_id,
                      'apportionment_type_id' => $item->apportionment_type_id,
                      'value' => $item->value,
                      'is_locked' => $item->is_locked,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function supplierCreditFacilities()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('supplier_credit_facilities')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'vendor_registration_id' => $item->vendor_registration_id,
                      'supplier_name' => $item->supplier_name,
                      'credit_facilities' => $item->credit_facilities,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementSiteDiaryMachinery()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_site_diary_machinery')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'machinery_id' => $item->machinery_id,
                      'site_diary_id' => $item->site_diary_id,
                      'value' => $item->value,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function systemModuleElements()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('system_module_elements')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'has_attachments' => $item->has_attachments,
                      'element_definition_id' => $item->element_definition_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'is_key_information' => $item->is_key_information,
                      'created_at' => $item->created_at,
                      'label' => $item->label,
                      'instructions' => $item->instructions,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function supplierCreditFacilitySettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('supplier_credit_facility_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'has_attachments' => $item->has_attachments,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function systemModuleConfigurations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('system_module_configurations')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'module_id' => $item->module_id,
                      'is_enabled' => $item->is_enabled,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementSiteDiaryRejectedMaterials()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_site_diary_rejected_materials')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'rejected_material_id' => $item->rejected_material_id,
                      'site_diary_id' => $item->site_diary_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementSiteDiaryVisitors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_site_diary_visitors')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'site_diary_id' => $item->site_diary_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'visitor_time_out' => $item->visitor_time_out,
                      'visitor_name' => $item->visitor_name,
                      'visitor_company_name' => $item->visitor_company_name,
                      'visitor_time_in' => $item->visitor_time_in,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function structuredDocuments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('structured_documents')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'is_template' => $item->is_template,
                      'object_id' => $item->object_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'margin_top' => $item->margin_top,
                      'margin_bottom' => $item->margin_bottom,
                      'margin_left' => $item->margin_left,
                      'margin_right' => $item->margin_right,
                      'font_size' => $item->font_size,
                      'title' => $item->title,
                      'heading' => $item->heading,
                      'footer_text' => $item->footer_text,
                      'object_type' => $item->object_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function systemSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('system_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'allow_other_business_entity_types' => $item->allow_other_business_entity_types,
                      'allow_other_property_developers' => $item->allow_other_property_developers,
                      'allow_other_vpe_project_removal_reasons' => $item->allow_other_vpe_project_removal_reasons,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function technicalEvaluationResponseLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('technical_evaluation_response_log')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'set_reference_id' => $item->set_reference_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function technicalEvaluations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('technical_evaluations')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'targeted_date_of_award' => $item->targeted_date_of_award,
                      'submitted_by' => $item->submitted_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function templateTenderDocumentFolders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('template_tender_document_folders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'folder_type' => $item->folder_type,
                      'root_id' => $item->root_id,
                      'parent_id' => $item->parent_id,
                      'lft' => $item->lft,
                      'rgt' => $item->rgt,
                      'depth' => $item->depth,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderDocumentDownloadLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_document_download_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_document_id' => $item->tender_document_id,
                      'company_id' => $item->company_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderCallingTenderInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_calling_tender_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'date_of_calling_tender' => $item->date_of_calling_tender,
                      'date_of_closing_tender' => $item->date_of_closing_tender,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'disable_tender_rates_submission' => $item->disable_tender_rates_submission,
                      'technical_tender_closing_date' => $item->technical_tender_closing_date,
                      'allow_contractor_propose_own_completion_period' => $item->allow_contractor_propose_own_completion_period,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tags()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tags')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'category' => $item->category,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function technicalEvaluationSetReferences()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('technical_evaluation_set_references')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'set_id' => $item->set_id,
                      'work_category_id' => $item->work_category_id,
                      'contract_limit_id' => $item->contract_limit_id,
                      'project_id' => $item->project_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'hidden' => $item->hidden,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function technicalEvaluationAttachments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('technical_evaluation_attachments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'upload_id' => $item->upload_id,
                      'company_id' => $item->company_id,
                      'item_id' => $item->item_id,
                      'id' => $item->id,
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'filename' => $item->filename,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function technicalEvaluationItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('technical_evaluation_items')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'parent_id' => $item->parent_id,
                      'id' => $item->id,
                      'value' => $item->value,
                      'type' => $item->type,
                      'compulsory' => $item->compulsory,
                      'created_at' => $item->created_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function technicalEvaluationTendererOptions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('technical_evaluation_tenderer_options')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'option_id' => $item->option_id,
                      'item_id' => $item->item_id,
                      'company_id' => $item->company_id,
                      'id' => $item->id,
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function technicalEvaluationVerifierLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('technical_evaluation_verifier_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'user_id' => $item->user_id,
                      'type' => $item->type,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function templateTenderDocumentFolderWorkCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('template_tender_document_folder_work_category')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'template_tender_document_folder_id' => $item->template_tender_document_folder_id,
                      'work_category_id' => $item->work_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function templateTenderDocumentFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('template_tender_document_files')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'work_category_id' => $item->work_category_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'cabinet_file_id' => $item->cabinet_file_id,
                      'folder_id' => $item->folder_id,
                      'parent_id' => $item->parent_id,
                      'created_at' => $item->created_at,
                      'filename' => $item->filename,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function templateTenderDocumentFilesRolesReadonly()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('template_tender_document_files_roles_readonly')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'template_tender_document_file_id' => $item->template_tender_document_file_id,
                      'contract_group_id' => $item->contract_group_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderAlternativesPosition()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_alternatives_position')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'position' => $item->position,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'form_of_tender_id' => $item->form_of_tender_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderCallingTenderInformationUser()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_calling_tender_information_user')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_calling_tender_information_id' => $item->tender_calling_tender_information_id,
                      'user_id' => $item->user_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderInterviewInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_interview_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'date_and_time' => $item->date_and_time,
                      'contract_group_id' => $item->contract_group_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderLotInformationUser()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_lot_information_user')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_lot_information_id' => $item->tender_lot_information_id,
                      'user_id' => $item->user_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderRotInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_rot_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'proposed_date_of_calling_tender' => $item->proposed_date_of_calling_tender,
                      'proposed_date_of_closing_tender' => $item->proposed_date_of_closing_tender,
                      'target_date_of_site_possession' => $item->target_date_of_site_possession,
                      'budget' => $item->budget,
                      'consultant_estimates' => $item->consultant_estimates,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'completion_period' => $item->completion_period,
                      'project_incentive_percentage' => $item->project_incentive_percentage,
                      'allow_contractor_propose_own_completion_period' => $item->allow_contractor_propose_own_completion_period,
                      'technical_evaluation_required' => $item->technical_evaluation_required,
                      'contract_limit_id' => $item->contract_limit_id,
                      'completion_period_metric' => $item->completion_period_metric,
                      'disable_tender_rates_submission' => $item->disable_tender_rates_submission,
                      'procurement_method_id' => $item->procurement_method_id,
                      'technical_tender_closing_date' => $item->technical_tender_closing_date,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderRotInformationUser()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_rot_information_user')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_rot_information_id' => $item->tender_rot_information_id,
                      'user_id' => $item->user_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderDocumentFiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_document_files')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'cabinet_file_id' => $item->cabinet_file_id,
                      'tender_document_folder_id' => $item->tender_document_folder_id,
                      'revision' => $item->revision,
                      'parent_id' => $item->parent_id,
                      'filename' => $item->filename,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderLotInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_lot_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'date_of_calling_tender' => $item->date_of_calling_tender,
                      'date_of_closing_tender' => $item->date_of_closing_tender,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'completion_period' => $item->completion_period,
                      'project_incentive_percentage' => $item->project_incentive_percentage,
                      'allow_contractor_propose_own_completion_period' => $item->allow_contractor_propose_own_completion_period,
                      'technical_evaluation_required' => $item->technical_evaluation_required,
                      'contract_limit_id' => $item->contract_limit_id,
                      'disable_tender_rates_submission' => $item->disable_tender_rates_submission,
                      'procurement_method_id' => $item->procurement_method_id,
                      'technical_tender_closing_date' => $item->technical_tender_closing_date,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderDocumentFilesRolesReadonly()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_document_files_roles_readonly')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_document_file_id' => $item->tender_document_file_id,
                      'contract_group_id' => $item->contract_group_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderDocumentFolders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_document_folders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'folder_type' => $item->folder_type,
                      'root_id' => $item->root_id,
                      'parent_id' => $item->parent_id,
                      'lft' => $item->lft,
                      'rgt' => $item->rgt,
                      'depth' => $item->depth,
                      'priority' => $item->priority,
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'system_generated_folder' => $item->system_generated_folder,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderFormVerifierLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_form_verifier_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'loggable_id' => $item->loggable_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'user_id' => $item->user_id,
                      'type' => $item->type,
                      'loggable_type' => $item->loggable_type,
                      'verifier_remark' => $item->verifier_remark,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderInterviews()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_interviews')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'tender_interview_information_id' => $item->tender_interview_information_id,
                      'tender_id' => $item->tender_id,
                      'company_id' => $item->company_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'date_and_time' => $item->date_and_time,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'venue' => $item->venue,
                      'key' => $item->key,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderReminders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_reminders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'tender_stage' => $item->tender_stage,
                      'tender_id' => $item->tender_id,
                      'updated_by' => $item->updated_by,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderUserTechnicalEvaluationVerifier()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_user_technical_evaluation_verifier')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'user_id' => $item->user_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderUserVerifierOpenTender()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_user_verifier_open_tender')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'user_id' => $item->user_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderUserVerifierRetender()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_user_verifier_retender')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'tender_id' => $item->tender_id,
                      'user_id' => $item->user_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tendererTechnicalEvaluationInformation()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tenderer_technical_evaluation_information')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'shortlisted' => $item->shortlisted,
                      'company_id' => $item->company_id,
                      'tender_id' => $item->tender_id,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tendererTechnicalEvaluationInformationLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tenderer_technical_evaluation_information_log')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'information_id' => $item->information_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function themeSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('theme_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'active' => $item->active,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'logo2' => $item->logo2,
                      'bg_image' => $item->bg_image,
                      'logo1' => $item->logo1,
                      'theme_colour1' => $item->theme_colour1,
                      'theme_colour2' => $item->theme_colour2,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function users()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('users')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'is_gp_admin' => $item->is_gp_admin,
                      'company_id' => $item->company_id,
                      'is_admin' => $item->is_admin,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'account_blocked_status' => $item->account_blocked_status,
                      'allow_access_to_buildspace' => $item->allow_access_to_buildspace,
                      'password_updated_at' => $item->password_updated_at,
                      'purge_date' => $item->purge_date,
                      'allow_access_to_gp' => $item->allow_access_to_gp,
                      'id' => $item->id,
                      'confirmed' => $item->confirmed,
                      'is_super_admin' => $item->is_super_admin,
                      'name' => $item->name,
                      'contact_number' => $item->contact_number,
                      'username' => $item->username,
                      'email' => $item->email,
                      'password' => $item->password,
                      'confirmation_code' => $item->confirmation_code,
                      'remember_token' => $item->remember_token,
                      'gp_access_token' => $item->gp_access_token,
                      'designation' => $item->designation,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function userCompanyLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('user_company_log')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'company_id' => $item->company_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function trackRecordProjects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('track_record_projects')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'country_id' => $item->country_id,
                      'conquas_year_of_achievement' => $item->conquas_year_of_achievement,
                      'year_of_recognition_awards' => $item->year_of_recognition_awards,
                      'vendor_registration_id' => $item->vendor_registration_id,
                      'vendor_category_id' => $item->vendor_category_id,
                      'shassic_score' => $item->shassic_score,
                      'project_amount' => $item->project_amount,
                      'id' => $item->id,
                      'property_developer_id' => $item->property_developer_id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'type' => $item->type,
                      'year_of_site_possession' => $item->year_of_site_possession,
                      'year_of_completion' => $item->year_of_completion,
                      'has_qlassic_or_conquas_score' => $item->has_qlassic_or_conquas_score,
                      'qlassic_year_of_achievement' => $item->qlassic_year_of_achievement,
                      'title' => $item->title,
                      'conquas_score' => $item->conquas_score,
                      'property_developer_text' => $item->property_developer_text,
                      'project_amount_remarks' => $item->project_amount_remarks,
                      'qlassic_score' => $item->qlassic_score,
                      'awards_received' => $item->awards_received,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'target' => $item->target,
                      'hidden' => $item->hidden,
                      'name' => $item->name,
                      'code' => $item->code,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorDetailSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_detail_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'contract_group_category_instructions' => $item->contract_group_category_instructions,
                      'vendor_category_instructions' => $item->vendor_category_instructions,
                      'contact_person_instructions' => $item->contact_person_instructions,
                      'reference_number_instructions' => $item->reference_number_instructions,
                      'tax_registration_number_instructions' => $item->tax_registration_number_instructions,
                      'email_instructions' => $item->email_instructions,
                      'telephone_instructions' => $item->telephone_instructions,
                      'fax_instructions' => $item->fax_instructions,
                      'country_instructions' => $item->country_instructions,
                      'state_instructions' => $item->state_instructions,
                      'company_status_instructions' => $item->company_status_instructions,
                      'bumiputera_equity_instructions' => $item->bumiputera_equity_instructions,
                      'non_bumiputera_equity_instructions' => $item->non_bumiputera_equity_instructions,
                      'foreigner_equity_instructions' => $item->foreigner_equity_instructions,
                      'cidb_grade_instructions' => $item->cidb_grade_instructions,
                      'bim_level_instructions' => $item->bim_level_instructions,
                      'name_instructions' => $item->name_instructions,
                      'address_instructions' => $item->address_instructions,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenders()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tenders')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'technical_evaluation_status' => $item->technical_evaluation_status,
                      'project_id' => $item->project_id,
                      'count' => $item->count,
                      'current_form_type' => $item->current_form_type,
                      'tender_starting_date' => $item->tender_starting_date,
                      'tender_closing_date' => $item->tender_closing_date,
                      'retender_status' => $item->retender_status,
                      'retender_verification_status' => $item->retender_verification_status,
                      'open_tender_status' => $item->open_tender_status,
                      'open_tender_verification_status' => $item->open_tender_verification_status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'validity_period_in_days' => $item->validity_period_in_days,
                      'technical_evaluation_verification_status' => $item->technical_evaluation_verification_status,
                      'technical_tender_closing_date' => $item->technical_tender_closing_date,
                      'currently_selected_tenderer_id' => $item->currently_selected_tenderer_id,
                      'request_retender_at' => $item->request_retender_at,
                      'request_retender_by' => $item->request_retender_by,
                      'id' => $item->id,
                      'request_retender_remarks' => $item->request_retender_remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function uploads()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('uploads')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'size' => $item->size,
                      'user_id' => $item->user_id,
                      'parent_id' => $item->parent_id,
                      'deleted_at' => $item->deleted_at,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'filename' => $item->filename,
                      'path' => $item->path,
                      'mobile_sync_uuid' => $item->mobile_sync_uuid,
                      'extension' => $item->extension,
                      'mimetype' => $item->mimetype,
                      'original_file_name' => $item->original_file_name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function userLogins()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('user_logins')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function userSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('user_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'language_id' => $item->language_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function usersCompanyVerificationPrivileges()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('users_company_verification_privileges')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorCategoryTemporaryRecords()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_category_temporary_records')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_registration_id' => $item->vendor_registration_id,
                      'vendor_category_id' => $item->vendor_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorCategoryVendorWorkCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_category_vendor_work_category')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_category_id' => $item->vendor_category_id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorEvaluationCycleScores()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_evaluation_cycle_scores')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'company_id' => $item->company_id,
                      'vendor_performance_evaluation_cycle_id' => $item->vendor_performance_evaluation_cycle_id,
                      'score' => $item->score,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deliberated_score' => $item->deliberated_score,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorEvaluationScores()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_evaluation_scores')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'company_id' => $item->company_id,
                      'vendor_performance_evaluation_id' => $item->vendor_performance_evaluation_id,
                      'score' => $item->score,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorManagementInstructionSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_management_instruction_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'supplier_credit_facilities' => $item->supplier_credit_facilities,
                      'payment' => $item->payment,
                      'vendor_pre_qualifications' => $item->vendor_pre_qualifications,
                      'company_personnel' => $item->company_personnel,
                      'project_track_record' => $item->project_track_record,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationFormChangeLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_form_change_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'vendor_performance_evaluation_setup_id' => $item->vendor_performance_evaluation_setup_id,
                      'old_template_node_id' => $item->old_template_node_id,
                      'new_template_node_id' => $item->new_template_node_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorManagementGradeLevels()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_management_grade_levels')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'score_upper_limit' => $item->score_upper_limit,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'description' => $item->description,
                      'definition' => $item->definition,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationModuleParameters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_module_parameters')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'default_time_frame_for_vpe_cycle_value' => $item->default_time_frame_for_vpe_cycle_value,
                      'default_time_frame_for_vpe_cycle_unit' => $item->default_time_frame_for_vpe_cycle_unit,
                      'default_time_frame_for_vpe_submission_value' => $item->default_time_frame_for_vpe_submission_value,
                      'default_time_frame_for_vpe_submission_unit' => $item->default_time_frame_for_vpe_submission_unit,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'attachments_required' => $item->attachments_required,
                      'attachments_required_score_threshold' => $item->attachments_required_score_threshold,
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                      'passing_score' => $item->passing_score,
                      'email_reminder_before_cycle_end_date' => $item->email_reminder_before_cycle_end_date,
                      'email_reminder_before_cycle_end_date_value' => $item->email_reminder_before_cycle_end_date_value,
                      'email_reminder_before_cycle_end_date_unit' => $item->email_reminder_before_cycle_end_date_unit,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationProjectRemovalReasons()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_project_removal_reasons')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'hidden' => $item->hidden,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationSubmissionReminderSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_submission_reminder_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'number_of_days_before' => $item->number_of_days_before,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorManagementGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_management_grades')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_by' => $item->updated_by,
                      'is_template' => $item->is_template,
                      'created_by' => $item->created_by,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorManagementUserPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_management_user_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'type' => $item->type,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationCompanyFormEvaluationLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_company_form_evaluation_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_performance_evaluation_company_form_id' => $item->vendor_performance_evaluation_company_form_id,
                      'action_type' => $item->action_type,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationCompanyForms()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_company_forms')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'submitted_for_approval_by' => $item->submitted_for_approval_by,
                      'vendor_performance_evaluation_id' => $item->vendor_performance_evaluation_id,
                      'company_id' => $item->company_id,
                      'weighted_node_id' => $item->weighted_node_id,
                      'evaluator_company_id' => $item->evaluator_company_id,
                      'status_id' => $item->status_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'id' => $item->id,
                      'deleted_at' => $item->deleted_at,
                      'score' => $item->score,
                      'evaluator_remarks' => $item->evaluator_remarks,
                      'processor_remarks' => $item->processor_remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationFormChangeRequests()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_form_change_requests')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'deleted_at' => $item->deleted_at,
                      'user_id' => $item->user_id,
                      'vendor_performance_evaluation_setup_id' => $item->vendor_performance_evaluation_setup_id,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationProcessorEditDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_processor_edit_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_performance_evaluation_processor_edit_log_id' => $item->vendor_performance_evaluation_processor_edit_log_id,
                      'weighted_node_id' => $item->weighted_node_id,
                      'previous_score_id' => $item->previous_score_id,
                      'is_previous_node_excluded' => $item->is_previous_node_excluded,
                      'current_score_id' => $item->current_score_id,
                      'is_current_node_excluded' => $item->is_current_node_excluded,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationProcessorEditLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_processor_edit_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_performance_evaluation_company_form_id' => $item->vendor_performance_evaluation_company_form_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationRemovalRequests()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_removal_requests')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'vendor_performance_evaluation_id' => $item->vendor_performance_evaluation_id,
                      'user_id' => $item->user_id,
                      'vendor_performance_evaluation_project_removal_reason_id' => $item->vendor_performance_evaluation_project_removal_reason_id,
                      'deleted_at' => $item->deleted_at,
                      'removed_at' => $item->removed_at,
                      'action_by' => $item->action_by,
                      'evaluation_removed' => $item->evaluation_removed,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'vendor_performance_evaluation_project_removal_reason_text' => $item->vendor_performance_evaluation_project_removal_reason_text,
                      'request_remarks' => $item->request_remarks,
                      'dismissal_remarks' => $item->dismissal_remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationSetups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_setups')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_performance_evaluation_id' => $item->vendor_performance_evaluation_id,
                      'company_id' => $item->company_id,
                      'template_node_id' => $item->template_node_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationTemplateForms()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_template_forms')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_status_id' => $item->project_status_id,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'weighted_node_id' => $item->weighted_node_id,
                      'revision' => $item->revision,
                      'status_id' => $item->status_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'original_form_id' => $item->original_form_id,
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                      'current_selected_revision' => $item->current_selected_revision,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorProfileModuleParameters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_profile_module_parameters')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'validity_period_of_active_vendor_in_avl_value' => $item->validity_period_of_active_vendor_in_avl_value,
                      'validity_period_of_active_vendor_in_avl_unit' => $item->validity_period_of_active_vendor_in_avl_unit,
                      'grace_period_of_expired_vendor_before_moving_to_dvl_value' => $item->grace_period_of_expired_vendor_before_moving_to_dvl_value,
                      'grace_period_of_expired_vendor_before_moving_to_dvl_unit' => $item->grace_period_of_expired_vendor_before_moving_to_dvl_unit,
                      'vendor_retain_period_in_wl_value' => $item->vendor_retain_period_in_wl_value,
                      'vendor_retain_period_in_wl_unit' => $item->vendor_retain_period_in_wl_unit,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'renewal_period_before_expiry_in_days' => $item->renewal_period_before_expiry_in_days,
                      'watch_list_nomineee_to_active_vendor_list_threshold_score' => $item->watch_list_nomineee_to_active_vendor_list_threshold_score,
                      'watch_list_nomineee_to_watch_list_threshold_score' => $item->watch_list_nomineee_to_watch_list_threshold_score,
                      'registration_price' => $item->registration_price,
                      'renewal_price' => $item->renewal_price,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorProfiles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_profiles')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorRegistrationAndPrequalificationModuleParameters()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_registration_and_prequalification_module_parameters')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'notify_vendors_for_renewal_unit' => $item->notify_vendors_for_renewal_unit,
                      'valid_period_of_temp_login_acc_to_unreg_vendor_value' => $item->valid_period_of_temp_login_acc_to_unreg_vendor_value,
                      'valid_period_of_temp_login_acc_to_unreg_vendor_unit' => $item->valid_period_of_temp_login_acc_to_unreg_vendor_unit,
                      'allow_only_one_comp_to_reg_under_multi_vendor_group' => $item->allow_only_one_comp_to_reg_under_multi_vendor_group,
                      'allow_only_one_comp_to_reg_under_multi_vendor_category' => $item->allow_only_one_comp_to_reg_under_multi_vendor_category,
                      'vendor_reg_cert_generated_sent_to_successful_reg_vendor' => $item->vendor_reg_cert_generated_sent_to_successful_reg_vendor,
                      'notify_vendor_before_end_of_temp_acc_valid_period_value' => $item->notify_vendor_before_end_of_temp_acc_valid_period_value,
                      'notify_vendor_before_end_of_temp_acc_valid_period_unit' => $item->notify_vendor_before_end_of_temp_acc_valid_period_unit,
                      'period_retain_unsuccessful_reg_and_preq_submission_value' => $item->period_retain_unsuccessful_reg_and_preq_submission_value,
                      'period_retain_unsuccessful_reg_and_preq_submission_unit' => $item->period_retain_unsuccessful_reg_and_preq_submission_unit,
                      'start_period_retain_unsuccessful_reg_and_preq_submission_value' => $item->start_period_retain_unsuccessful_reg_and_preq_submission_value,
                      'notify_purge_data_before_end_period_for_unsuccessful_sub_value' => $item->notify_purge_data_before_end_period_for_unsuccessful_sub_value,
                      'notify_purge_data_before_end_period_for_unsuccessful_sub_unit' => $item->notify_purge_data_before_end_period_for_unsuccessful_sub_unit,
                      'retain_info_of_unsuccessfully_reg_vendor_after_data_purge' => $item->retain_info_of_unsuccessfully_reg_vendor_after_data_purge,
                      'retain_company_name' => $item->retain_company_name,
                      'retain_roc_number' => $item->retain_roc_number,
                      'retain_email' => $item->retain_email,
                      'retain_contact_number' => $item->retain_contact_number,
                      'retain_date_of_data_purging' => $item->retain_date_of_data_purging,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                      'valid_submission_days' => $item->valid_submission_days,
                      'notify_vendors_for_renewal_value' => $item->notify_vendors_for_renewal_value,
                      'vendor_declaration' => $item->vendor_declaration,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorRegistrationFormTemplateMappings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_registration_form_template_mappings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'business_entity_type_id' => $item->business_entity_type_id,
                      'dynamic_form_id' => $item->dynamic_form_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorRegistrationSections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_registration_sections')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'is_section_applicable' => $item->is_section_applicable,
                      'vendor_registration_id' => $item->vendor_registration_id,
                      'section' => $item->section,
                      'status_id' => $item->status_id,
                      'amendment_status' => $item->amendment_status,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'amendment_remarks' => $item->amendment_remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorRegistrationSubmissionLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_registration_submission_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_registration_id' => $item->vendor_registration_id,
                      'action_type' => $item->action_type,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluations')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_performance_evaluation_cycle_id' => $item->vendor_performance_evaluation_cycle_id,
                      'project_id' => $item->project_id,
                      'project_status_id' => $item->project_status_id,
                      'status_id' => $item->status_id,
                      'person_in_charge_id' => $item->person_in_charge_id,
                      'start_date' => $item->start_date,
                      'end_date' => $item->end_date,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                      'type' => $item->type,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'deleted_by' => $item->deleted_by,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluators()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluators')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_performance_evaluation_id' => $item->vendor_performance_evaluation_id,
                      'company_id' => $item->company_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPreQualificationSetups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_pre_qualification_setups')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_category_id' => $item->vendor_category_id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'pre_qualification_required' => $item->pre_qualification_required,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPreQualificationTemplateForms()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_pre_qualification_template_forms')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'weighted_node_id' => $item->weighted_node_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'status_id' => $item->status_id,
                      'revision' => $item->revision,
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPreQualificationVendorGroupGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_pre_qualification_vendor_group_grades')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPreQualifications()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_pre_qualifications')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'weighted_node_id' => $item->weighted_node_id,
                      'status_id' => $item->status_id,
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                      'vendor_registration_id' => $item->vendor_registration_id,
                      'template_form_id' => $item->template_form_id,
                      'deleted_at' => $item->deleted_at,
                      'score' => $item->score,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorProfileRemarks()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_profile_remarks')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_by' => $item->created_by,
                      'vendor_profile_id' => $item->vendor_profile_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'content' => $item->content,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorRegistrationPayments()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_registration_payments')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'payment_setting_id' => $item->payment_setting_id,
                      'running_number' => $item->running_number,
                      'currently_selected' => $item->currently_selected,
                      'submitted' => $item->submitted,
                      'paid' => $item->paid,
                      'successful' => $item->successful,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'submitted_date' => $item->submitted_date,
                      'paid_date' => $item->paid_date,
                      'successful_date' => $item->successful_date,
                      'status' => $item->status,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorRegistrationProcessors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_registration_processors')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_registration_id' => $item->vendor_registration_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function weatherRecordReports()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('weather_record_reports')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'weather_record_id' => $item->weather_record_id,
                      'created_by' => $item->created_by,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                      'weather_status' => $item->weather_status,
                      'created_at' => $item->created_at,
                      'from_time' => $item->from_time,
                      'to_time' => $item->to_time,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorTypeChangeLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_type_change_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_id' => $item->vendor_id,
                      'old_type' => $item->old_type,
                      'new_type' => $item->new_type,
                      'vendor_evaluation_cycle_score_id' => $item->vendor_evaluation_cycle_score_id,
                      'watch_list_entry_date' => $item->watch_list_entry_date,
                      'watch_list_release_date' => $item->watch_list_release_date,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function weightedNodeScores()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('weighted_node_scores')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'amendments_required' => $item->amendments_required,
                      'node_id' => $item->node_id,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'value' => $item->value,
                      'is_selected' => $item->is_selected,
                      'created_at' => $item->created_at,
                      'name' => $item->name,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function workCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('work_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                      'enabled' => $item->enabled,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'name' => $item->name,
                      'identifier' => $item->identifier,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function workSubcategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('work_subcategories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function weathers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('weathers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorWorkCategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_work_categories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'hidden' => $item->hidden,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                      'code' => $item->code,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorWorkSubcategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_work_subcategories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'hidden' => $item->hidden,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'name' => $item->name,
                      'code' => $item->code,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function weightedNodes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('weighted_nodes')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'is_excluded' => $item->is_excluded,
                      'parent_id' => $item->parent_id,
                      'lft' => $item->lft,
                      'rgt' => $item->rgt,
                      'depth' => $item->depth,
                      'amendments_required' => $item->amendments_required,
                      'id' => $item->id,
                      'weight' => $item->weight,
                      'root_id' => $item->root_id,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendors()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendors')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'company_id' => $item->company_id,
                      'type' => $item->type,
                      'vendor_evaluation_cycle_score_id' => $item->vendor_evaluation_cycle_score_id,
                      'is_qualified' => $item->is_qualified,
                      'watch_list_entry_date' => $item->watch_list_entry_date,
                      'watch_list_release_date' => $item->watch_list_release_date,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorWorkCategoryWorkCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_work_category_work_category')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'vendor_work_category_id' => $item->vendor_work_category_id,
                      'work_category_id' => $item->work_category_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function verifiers()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('verifiers')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'deleted_at' => $item->deleted_at,
                      'verifier_id' => $item->verifier_id,
                      'object_id' => $item->object_id,
                      'days_to_verify' => $item->days_to_verify,
                      'id' => $item->id,
                      'sequence_number' => $item->sequence_number,
                      'approved' => $item->approved,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'verified_at' => $item->verified_at,
                      'start_at' => $item->start_at,
                      'object_type' => $item->object_type,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function weatherRecords()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('weather_records')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'created_by' => $item->created_by,
                      'verified_by' => $item->verified_by,
                      'date' => $item->date,
                      'deleted_at' => $item->deleted_at,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'note' => $item->note,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function accessLog()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('access_log')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'params' => $item->params,
                      'http_method' => $item->http_method,
                      'url' => $item->url,
                      'ip_address' => $item->ip_address,
                      'user_agent' => $item->user_agent,
                      'url_path' => $item->url_path,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function projects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('projects')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'e_bidding' => $item->e_bidding,
                      'business_unit_id' => $item->business_unit_id,
                      'contract_id' => $item->contract_id,
                      'contractor_access_enabled' => $item->contractor_access_enabled,
                      'skipped_to_post_contract' => $item->skipped_to_post_contract,
                      'contractor_contractual_claim_access_enabled' => $item->contractor_contractual_claim_access_enabled,
                      'parent_project_id' => $item->parent_project_id,
                      'deleted_at' => $item->deleted_at,
                      'open_tender' => $item->open_tender,
                      'id' => $item->id,
                      'running_number' => $item->running_number,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'status_id' => $item->status_id,
                      'country_id' => $item->country_id,
                      'state_id' => $item->state_id,
                      'completion_date' => $item->completion_date,
                      'work_category_id' => $item->work_category_id,
                      'current_tender_status' => $item->current_tender_status,
                      'subsidiary_id' => $item->subsidiary_id,
                      'title' => $item->title,
                      'reference' => $item->reference,
                      'address' => $item->address,
                      'description' => $item->description,
                      'modified_currency_name' => $item->modified_currency_name,
                      'reference_suffix' => $item->reference_suffix,
                      'modified_currency_code' => $item->modified_currency_code,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function accountingReportExportLogDetails()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('accounting_report_export_log_details')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'accounting_report_export_log_id' => $item->accounting_report_export_log_id,
                      'project_code_setting_id' => $item->project_code_setting_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function interimClaims()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('interim_claims')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'month' => $item->month,
                      'year' => $item->year,
                      'issue_certificate_deadline' => $item->issue_certificate_deadline,
                      'amount_claimed' => $item->amount_claimed,
                      'amount_granted' => $item->amount_granted,
                      'claim_counter' => $item->claim_counter,
                      'status' => $item->status,
                      'claim_no' => $item->claim_no,
                      'note' => $item->note,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function architectInstructions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('architect_instructions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'deadline_to_comply' => $item->deadline_to_comply,
                      'status' => $item->status,
                      'steps' => $item->steps,
                      'reference' => $item->reference,
                      'instruction' => $item->instruction,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function aeSecondLevelMessages()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('ae_second_level_messages')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'additional_expense_id' => $item->additional_expense_id,
                      'created_by' => $item->created_by,
                      'created_at' => $item->created_at,
                      'id' => $item->id,
                      'requested_new_deadline' => $item->requested_new_deadline,
                      'grant_different_deadline' => $item->grant_different_deadline,
                      'decision' => $item->decision,
                      'type' => $item->type,
                      'subject' => $item->subject,
                      'message' => $item->message,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companies()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('companies')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'bim_level_id' => $item->bim_level_id,
                      'bumiputera_equity' => $item->bumiputera_equity,
                      'non_bumiputera_equity' => $item->non_bumiputera_equity,
                      'foreigner_equity' => $item->foreigner_equity,
                      'expiry_date' => $item->expiry_date,
                      'activation_date' => $item->activation_date,
                      'third_party_vendor_id' => $item->third_party_vendor_id,
                      'deactivation_date' => $item->deactivation_date,
                      'deactivated_at' => $item->deactivated_at,
                      'vendor_status' => $item->vendor_status,
                      'company_status' => $item->company_status,
                      'cidb_grade' => $item->cidb_grade,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'country_id' => $item->country_id,
                      'state_id' => $item->state_id,
                      'contract_group_category_id' => $item->contract_group_category_id,
                      'confirmed' => $item->confirmed,
                      'business_entity_type_id' => $item->business_entity_type_id,
                      'purge_date' => $item->purge_date,
                      'is_bumiputera' => $item->is_bumiputera,
                      'name' => $item->name,
                      'address' => $item->address,
                      'main_contact' => $item->main_contact,
                      'email' => $item->email,
                      'telephone_number' => $item->telephone_number,
                      'fax_number' => $item->fax_number,
                      'third_party_app_identifier' => $item->third_party_app_identifier,
                      'business_entity_type_name' => $item->business_entity_type_name,
                      'tax_registration_no' => $item->tax_registration_no,
                      'tax_registration_id' => $item->tax_registration_id,
                      'reference_no' => $item->reference_no,
                      'reference_id' => $item->reference_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function countries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('countries')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'fips' => $item->fips,
                      'country' => $item->country,
                      'continent' => $item->continent,
                      'currency_code' => $item->currency_code,
                      'currency_name' => $item->currency_name,
                      'phone_prefix' => $item->phone_prefix,
                      'postal_code' => $item->postal_code,
                      'languages' => $item->languages,
                      'geonameid' => $item->geonameid,
                      'iso' => $item->iso,
                      'iso3' => $item->iso3,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function states()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('states')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'country_id' => $item->country_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                      'timezone' => $item->timezone,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function clauseItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('clause_items')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'clause_id' => $item->clause_id,
                      'id' => $item->id,
                      'priority' => $item->priority,
                      'description' => $item->description,
                      'no' => $item->no,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementAttachmentSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_attachment_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'consultant_management_contract_id' => $item->consultant_management_contract_id,
                      'id' => $item->id,
                      'mandatory' => $item->mandatory,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'title' => $item->title,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementOpenRfp()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_open_rfp')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_rfp_revision_id' => $item->consultant_management_rfp_revision_id,
                      'status' => $item->status,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementApprovalDocumentSectionA()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_approval_document_section_a')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'consultant_management_approval_document_id' => $item->consultant_management_approval_document_id,
                      'approving_authority' => $item->approving_authority,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorRegistrations()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_registrations')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'company_id' => $item->company_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'submitted_at' => $item->submitted_at,
                      'unsuccessful_at' => $item->unsuccessful_at,
                      'revision' => $item->revision,
                      'deleted_at' => $item->deleted_at,
                      'submission_type' => $item->submission_type,
                      'processor_remarks' => $item->processor_remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function companyPersonnel()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('company_personnel')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'type' => $item->type,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'vendor_registration_id' => $item->vendor_registration_id,
                      'years_of_experience' => $item->years_of_experience,
                      'amount_of_share' => $item->amount_of_share,
                      'holding_percentage' => $item->holding_percentage,
                      'amount_of_share_remarks' => $item->amount_of_share_remarks,
                      'holding_percentage_remarks' => $item->holding_percentage_remarks,
                      'name' => $item->name,
                      'identification_number' => $item->identification_number,
                      'email_address' => $item->email_address,
                      'contact_number' => $item->contact_number,
                      'years_of_experience_remarks' => $item->years_of_experience_remarks,
                      'designation' => $item->designation,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function consultantManagementCompanyRoles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('consultant_management_company_roles')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'role' => $item->role,
                      'consultant_management_contract_id' => $item->consultant_management_contract_id,
                      'company_id' => $item->company_id,
                      'calling_rfp' => $item->calling_rfp,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function productTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('product_types')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'title' => $item->title,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function developmentTypes()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('development_types')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_by' => $item->created_by,
                      'updated_by' => $item->updated_by,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'title' => $item->title,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function contractorWorkCategory()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('contractor_work_category')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'contractor_id' => $item->contractor_id,
                      'work_category_id' => $item->work_category_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function previousCpeGrades()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('previous_cpe_grades')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'grade' => $item->grade,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function dailyLabourReportLabourRates()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('daily_labour_report_labour_rates')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'labour_type' => $item->labour_type,
                      'normal_working_hours' => $item->normal_working_hours,
                      'normal_rate' => $item->normal_rate,
                      'ot_rate' => $item->ot_rate,
                      'normal_workers_total' => $item->normal_workers_total,
                      'ot_workers_total' => $item->ot_workers_total,
                      'ot_hours_total' => $item->ot_hours_total,
                      'total_cost' => $item->total_cost,
                      'daily_labour_report_id' => $item->daily_labour_report_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function emailNotificationRecipients()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('email_notification_recipients')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'email_notification_id' => $item->email_notification_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function externalApplicationClientModules()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('external_application_client_modules')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'client_id' => $item->client_id,
                      'outbound_status' => $item->outbound_status,
                      'outbound_only_same_source' => $item->outbound_only_same_source,
                      'downstream_permission' => $item->downstream_permission,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'module' => $item->module,
                      'outbound_url_path' => $item->outbound_url_path,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function formColumnSections()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('form_column_sections')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'form_column_id' => $item->form_column_id,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function forumThreadUserSettings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('forum_thread_user_settings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'forum_thread_user_id' => $item->forum_thread_user_id,
                      'keep_me_posted' => $item->keep_me_posted,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function indonesiaCivilContractEarlyWarnings()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('indonesia_civil_contract_early_warnings')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'project_id' => $item->project_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'commencement_date' => $item->commencement_date,
                      'reference' => $item->reference,
                      'impact' => $item->impact,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function indonesiaCivilContractEwEot()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('indonesia_civil_contract_ew_eot')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'indonesia_civil_contract_ew_id' => $item->indonesia_civil_contract_ew_id,
                      'indonesia_civil_contract_eot_id' => $item->indonesia_civil_contract_eot_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function letterOfAwardClauseCommentReadLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('letter_of_award_clause_comment_read_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'clause_comment_id' => $item->clause_comment_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function menus()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('menus')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'contract_id' => $item->contract_id,
                      'priority' => $item->priority,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'name' => $item->name,
                      'icon_class' => $item->icon_class,
                      'route_name' => $item->route_name,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function siteManagementDefects()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('site_management_defects')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'status_id' => $item->status_id,
                      'count_reject' => $item->count_reject,
                      'id' => $item->id,
                      'project_structure_location_code_id' => $item->project_structure_location_code_id,
                      'pre_defined_location_code_id' => $item->pre_defined_location_code_id,
                      'contractor_id' => $item->contractor_id,
                      'defect_category_id' => $item->defect_category_id,
                      'defect_id' => $item->defect_id,
                      'bill_column_setting_id' => $item->bill_column_setting_id,
                      'unit' => $item->unit,
                      'pic_user_id' => $item->pic_user_id,
                      'project_id' => $item->project_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'mcar_status' => $item->mcar_status,
                      'submitted_by' => $item->submitted_by,
                      'mobile_sync_uuid' => $item->mobile_sync_uuid,
                      'remark' => $item->remark,
                      'path_to_defect_photo' => $item->path_to_defect_photo,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function modulePermissionSubsidiaries()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('module_permission_subsidiaries')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'module_permission_id' => $item->module_permission_id,
                      'subsidiary_id' => $item->subsidiary_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function notificationsCategoriesInGroups()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('notifications_categories_in_groups')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'category_id' => $item->category_id,
                      'group_id' => $item->group_id,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function objectPermissions()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('object_permissions')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'user_id' => $item->user_id,
                      'object_id' => $item->object_id,
                      'id' => $item->id,
                      'is_editor' => $item->is_editor,
                      'created_at' => $item->created_at,
                      'object_type' => $item->object_type,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function openTenderAwardRecommendationReportEditLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('open_tender_award_recommendation_report_edit_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'open_tender_award_recommendation_id' => $item->open_tender_award_recommendation_id,
                      'user_id' => $item->user_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function structuredDocumentClauses()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('structured_document_clauses')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'updated_at' => $item->updated_at,
                      'id' => $item->id,
                      'is_editable' => $item->is_editable,
                      'parent_id' => $item->parent_id,
                      'priority' => $item->priority,
                      'structured_document_id' => $item->structured_document_id,
                      'created_at' => $item->created_at,
                      'content' => $item->content,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function technicalEvaluationAttachmentListItems()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('technical_evaluation_attachment_list_items')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'set_reference_id' => $item->set_reference_id,
                      'compulsory' => $item->compulsory,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'description' => $item->description,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function tenderInterviewLogs()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('tender_interview_logs')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'user_id' => $item->user_id,
                      'interview_id' => $item->interview_id,
                      'status' => $item->status,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function trackRecordProjectVendorWorkSubcategories()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('track_record_project_vendor_work_subcategories')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'track_record_project_id' => $item->track_record_project_id,
                      'vendor_work_subcategory_id' => $item->vendor_work_subcategory_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorPerformanceEvaluationCycles()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_performance_evaluation_cycles')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'vendor_management_grade_id' => $item->vendor_management_grade_id,
                      'start_date' => $item->start_date,
                      'end_date' => $item->end_date,
                      'id' => $item->id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'is_completed' => $item->is_completed,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }

    public function vendorRegistrationProcessorRemarks()
    {
        $authToken      = \Request::get('api_token') ?: GETENV('API_TOKEN');
        $expectedToken  = $this->expectedToken;

        if ($authToken !== $expectedToken) {
            return \Response::json(['message' => 'Not Authorized'], 401);
        }

        $items = DB::table('vendor_registration_processor_remarks')->get();

        if (\count($items) > 0) {
            return Response::json([
                'success' => true,
                'data' => array_map(function ($item) {
                    return [
                      'id' => $item->id,
                      'vendor_registration_processor_id' => $item->vendor_registration_processor_id,
                      'created_at' => $item->created_at,
                      'updated_at' => $item->updated_at,
                      'deleted_at' => $item->deleted_at,
                      'remarks' => $item->remarks,
                    ];
                }, (array)$items)
            ], 200);
        } else {
            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        }
    }
}
?>