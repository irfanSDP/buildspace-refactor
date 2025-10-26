<?php

use Carbon\Carbon;
use Illuminate\Support\MessageBag;

use PCK\Helpers\DateTime;
use PCK\Helpers\NumberHelper;
use PCK\Projects\Project;
use PCK\Projects\ProjectRepository;
use PCK\Projects\StatusType;
use PCK\Subsidiaries\Subsidiary;
use PCK\Countries\Country;
use PCK\WorkCategories\WorkCategory;
use PCK\Dashboard\DashboardGroup;
use PCK\ProcurementMethod\ProcurementMethod;
use PCK\EBiddings\EBidding;
use PCK\EBiddings\EBiddingMode;
use PCK\EBiddings\EBiddingStat;
use PCK\EBiddings\EBiddingConsoleRepository;

use PCK\Buildspace\NewPostContractFormInformation as bsNewPostContractFormInformation;

class DashboardController extends \BaseController {

    private $projectRepo;
    private $eBiddingConsoleRepo;

    public function __construct(
        ProjectRepository $projectRepo,
        EBiddingConsoleRepository $eBiddingConsoleRepo
    ) {
        $this->projectRepo = $projectRepo;
        $this->eBiddingConsoleRepo = $eBiddingConsoleRepo;
    }

    public function overview()
    {
        $inputs = Input::all();

        $user = Confide::user();

        if(!$user->dashboardGroup())
        {
            return Redirect::route('home.index');
        }

        $countries = $user->getDashboardCountries();

        $countryIds = (!empty($countries)) ? array_column($countries->toArray(), 'id') : [];

        if(array_key_exists('cid', $inputs) && in_array($inputs['cid'], $countryIds))
        {
            $selectCountryId = $inputs['cid'];
        }
        else
        {
            $selectCountryId = !empty($countryIds) ? $countryIds[0] : -1;
        }

        $selectedCountry = Country::find($selectCountryId);

        $months = [];
        for($m=1; $m<=12; ++$m)
        {
            $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
        }

        if(array_key_exists('fm', $inputs) && array_key_exists($inputs['fm'], $months))
        {
            $selectedFromMonth = $inputs['fm'];
        }
        else
        {
            $selectedFromMonth = 1;//defaulted to january
        }

        if(array_key_exists('tm', $inputs) && array_key_exists($inputs['tm'], $months))
        {
            $selectedToMonth = $inputs['tm'];
        }
        else
        {
            $selectedToMonth = date('n');
        }

        $years = ($selectedCountry) ? range(date('Y'), $user->dashboardGroup()->getMinYear($selectedCountry)) : [date('Y')];

        if(array_key_exists('fy', $inputs) && array_key_exists($inputs['fy'], $years))
        {
            $selectedFromYear = $inputs['fy'];
        }
        else
        {
            end($years);
            $selectedFromYear = key($years);
        }

        if(array_key_exists('ty', $inputs) && array_key_exists($inputs['ty'], $years))
        {
            $selectedToYear = $inputs['ty'];
        }
        else
        {
            reset($years);
            $selectedToYear = key($years);
        }

        $certifiedPaymentYears = [];
        $projectInfo = [];
        $overallBudgetRecords = [];

        if($selectedCountry)
        {
            switch($user->dashboardGroup()->type)
            {
                case DashboardGroup::TYPE_DEVELOPER:
                    $projectInfo = $user->getDeveloperDashboardADataByCountry($selectedCountry, [
                        'month' => $selectedFromMonth,
                        'year'  => $years[$selectedFromYear]
                    ],[
                        'month' => $selectedToMonth,
                        'year'  => $years[$selectedToYear]
                    ]);

                    $certifiedPayments = $user->getOverallCertifiedPayment($selectedCountry, [
                        'month' => $selectedFromMonth,
                        'year'  => $years[$selectedFromYear]
                    ],[
                        'month' => $selectedToMonth,
                        'year'  => $years[$selectedToYear]
                    ]);

                    $certifiedPaymentYears = array_keys($certifiedPayments);

                    rsort($certifiedPaymentYears);

                    unset($certifiedPayments);

                    $overallBudgetRecords = $user->getOverallBudgetVersusContractSumAndVOByWorkCategories($selectedCountry, [
                        'month' => $selectedFromMonth,
                        'year'  => $years[$selectedFromYear]
                    ],[
                        'month' => $selectedToMonth,
                        'year'  => $years[$selectedToYear]
                    ]);
                    break;
                case DashboardGroup::TYPE_MAIN_CONTRACTOR:
                    $projectInfo = $user->getMainContractorDashboardADataByCountry($selectedCountry, [
                        'month' => $selectedFromMonth,
                        'year'  => $years[$selectedFromYear]
                    ],[
                        'month' => $selectedToMonth,
                        'year'  => $years[$selectedToYear]
                    ]);

                    $overallBudgetRecords = [];
                    break;
            }
        }

        $dashboardType = 'overview';
        $formRoute = 'dashboard.overview';

        return View::make('dashboard.index', compact(
            'user',
            'countries',
            'selectedCountry',
            'months',
            'selectedFromMonth',
            'selectedToMonth',
            'years',
            'selectedFromYear',
            'selectedToYear',
            'projectInfo',
            'overallBudgetRecords',
            'certifiedPaymentYears',
            'dashboardType',
            'formRoute'
        ));
    }

    public function getOverallCertifiedPaymentAjax($countryId, $showByYear, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $user = Confide::user();

        if(!$user->dashboardGroup())
        {
            return Redirect::route('home.index');
        }

        $country = Country::find($countryId);

        $years = ($country) ? range(date('Y'), $user->dashboardGroup()->getMinYear($country)) : [date('Y')];

        $records = $user->getOverallCertifiedPayment($country, [
            'month' => $fromMonth,
            'year'  => $years[$fromYear]
        ],[
            'month' => $toMonth,
            'year'  => $years[$toYear]
        ]);

        $series = [
            'cost_vs_time'    => [],
            'cumulative_cost' => []
        ];

        foreach($records as $y => $record)
        {
            if((int)$showByYear > 0 && $showByYear != $y)
                continue;

            foreach($record as $key => $data)
            {
                $series['cost_vs_time'][] = [
                    date('M y', strtotime($y.'-'.$data['mon'].'-01')),
                    $data['total']
                ];

                $series['cumulative_cost'][] = [
                    date('M y', strtotime($y.'-'.$data['mon'].'-01')),
                    $data['cumulative_cost']
                ];
            }
        }

        return Response::json($series);
    }

    public function subsidiaries()
    {
        $inputs = Input::all();

        $user = Confide::user();

        if(!$user->dashboardGroup())
        {
            return Redirect::route('home.index');
        }

        $countries = $user->getDashboardCountries();

        $countryIds = (!empty($countries)) ? array_column($countries->toArray(), 'id') : [];

        if(array_key_exists('cid', $inputs) && in_array($inputs['cid'], $countryIds))
        {
            $selectCountryId = $inputs['cid'];
        }
        else
        {
            $selectCountryId = !empty($countryIds) ? $countryIds[0] : -1;
        }

        $selectedCountry = Country::find($selectCountryId);

        $months = [];
        for($m=1; $m<=12; ++$m)
        {
            $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
        }

        if(array_key_exists('fm', $inputs) && array_key_exists($inputs['fm'], $months))
        {
            $selectedFromMonth = $inputs['fm'];
        }
        else
        {
            $selectedFromMonth = 1;//defaulted to january
        }

        if(array_key_exists('tm', $inputs) && array_key_exists($inputs['tm'], $months))
        {
            $selectedToMonth = $inputs['tm'];
        }
        else
        {
            $selectedToMonth = date('n');
        }

        $years = ($selectedCountry) ? range(date('Y'), $user->dashboardGroup()->getMinYear($selectedCountry)) : [date('Y')];

        if(array_key_exists('fy', $inputs) && array_key_exists($inputs['fy'], $years))
        {
            $selectedFromYear = $inputs['fy'];
        }
        else
        {
            end($years);
            $selectedFromYear = key($years);
        }

        if(array_key_exists('ty', $inputs) && array_key_exists($inputs['ty'], $years))
        {
            $selectedToYear = $inputs['ty'];
        }
        else
        {
            reset($years);
            $selectedToYear = key($years);
        }

        $projectInfo            = [];
        $subsidiaryProjectCount = 0;
        $workCategories         = [];
        $overrunWorkCategories  = [];

        if($selectedCountry)
        {
            switch($user->dashboardGroup()->type)
            {
                case DashboardGroup::TYPE_DEVELOPER:
                    $subsidiaries = $user->getDashboardSubsidiaries($selectedCountry, [
                        'month' => $selectedFromMonth,
                        'year'  => $years[$selectedFromYear]
                    ],[
                        'month' => $selectedToMonth,
                        'year'  => $years[$selectedToYear]
                    ]);

                    $subsidiaryProjectCount = count($subsidiaries);

                    if($subsidiaryProjectCount)
                    {
                        $subsidiaryIds = [];
                        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($subsidiaries), RecursiveIteratorIterator::SELF_FIRST) as $k => $v)
                        {
                            if ($k === 'id')
                            {
                                $subsidiaryIds[] = $v;
                            }
                        }

                        $info = $user->getSubsidiariesSavingOrOverRunByWorkCategories($selectedCountry, $subsidiaryIds, [
                            'month' => $selectedFromMonth,
                            'year'  => $years[$selectedFromYear]
                        ],[
                            'month' => $selectedToMonth,
                            'year'  => $years[$selectedToYear]
                        ]);

                        $workCategoryIds = [];
                        $overrunWorkCategoryIds = [];

                        foreach($info as $records)
                        {
                            $workCategoryIds = array_merge($workCategoryIds, array_keys($records['work_categories']));

                            foreach($records['work_categories'] as $workCategoryId => $record)
                            {
                                if($record['overrun_amount'])
                                {
                                    $overrunWorkCategoryIds[] = $workCategoryId;
                                }
                            }
                        }

                        if(!empty($workCategoryIds))
                        {
                            $workCategories = WorkCategory::whereIn('work_categories.id', $workCategoryIds)
                            ->select('work_categories.id', 'work_categories.name')
                            ->distinct()
                            ->orderby('work_categories.name', 'ASC')
                            ->get();
                        }

                        if(!empty($overrunWorkCategoryIds))
                        {
                            $overrunWorkCategories = WorkCategory::whereIn('work_categories.id', $overrunWorkCategoryIds)
                            ->select('work_categories.id', 'work_categories.name')
                            ->distinct()
                            ->orderby('work_categories.name', 'ASC')
                            ->get();
                        }
                    }
                    break;
            }
        }

        $dashboardType = 'subsidiaries';
        $formRoute = 'dashboard.subsidiaries';

        return View::make('dashboard.index', compact(
            'user',
            'countries',
            'selectedCountry',
            'months',
            'selectedFromMonth',
            'selectedToMonth',
            'years',
            'selectedFromYear',
            'selectedToYear',
            'subsidiaryProjectCount',
            'workCategories',
            'overrunWorkCategories',
            'dashboardType',
            'formRoute'
        ));
    }

    public function statusSummary()
    {
        $inputs = Input::all();

        $user = Confide::user();

        if(!$user->dashboardGroup())
        {
            return Redirect::route('home.index');
        }

        $countries = $user->getDashboardCountries();

        $countryIds = (!empty($countries)) ? array_column($countries->toArray(), 'id') : [];

        if(array_key_exists('cid', $inputs) && in_array($inputs['cid'], $countryIds))
        {
            $selectCountryId = $inputs['cid'];
        }
        else
        {
            $selectCountryId = !empty($countryIds) ? $countryIds[0] : -1;
        }

        $selectedCountry = Country::find($selectCountryId);

        $months = [];
        for($m=1; $m<=12; ++$m)
        {
            $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
        }

        if(array_key_exists('fm', $inputs) && array_key_exists($inputs['fm'], $months))
        {
            $selectedFromMonth = $inputs['fm'];
        }
        else
        {
            $selectedFromMonth = 1;//defaulted to january
        }

        if(array_key_exists('tm', $inputs) && array_key_exists($inputs['tm'], $months))
        {
            $selectedToMonth = $inputs['tm'];
        }
        else
        {
            $selectedToMonth = date('n');
        }

        $years = ($selectedCountry) ? range(date('Y'), $user->dashboardGroup()->getMinYear($selectedCountry)) : [date('Y')];

        if(array_key_exists('fy', $inputs) && array_key_exists($inputs['fy'], $years))
        {
            $selectedFromYear = $inputs['fy'];
        }
        else
        {
            end($years);
            $selectedFromYear = key($years);
        }

        if(array_key_exists('ty', $inputs) && array_key_exists($inputs['ty'], $years))
        {
            $selectedToYear = $inputs['ty'];
        }
        else
        {
            reset($years);
            $selectedToYear = key($years);
        }

        $procurementMethods = ProcurementMethod::orderBy('name')->get();

        $dashboardType = 'statusSummary';
        $formRoute = 'dashboard.status.summary';

        // Temporary commented this statuses as the filter is just for closed tender and post contract projects.
        // If we were going to include other statuses for the dashboard filter then we need to remove this commented statuses
        $projectStatuses = [
            /*Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER => Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER_TEXT,
            Project::STATUS_TYPE_LIST_OF_TENDERER => Project::STATUS_TYPE_LIST_OF_TENDERER_TEXT,
            Project::STATUS_TYPE_CALLING_TENDER => Project::STATUS_TYPE_CALLING_TENDER_TEXT,*/
            Project::STATUS_TYPE_CLOSED_TENDER => Project::STATUS_TYPE_CLOSED_TENDER_TEXT,
            Project::STATUS_TYPE_POST_CONTRACT => Project::STATUS_TYPE_POST_CONTRACT_TEXT
        ];

        $eTenderWaiverStatuses = [
            bsNewPostContractFormInformation::E_TENDER_WAIVER_OPTION_SITE_URGENCY   => bsNewPostContractFormInformation::E_TENDER_WAIVER_OPTION_SITE_URGENCY_TEXT,
            bsNewPostContractFormInformation::E_TENDER_WAIVER_OPTION_INTER_COMPANY  => bsNewPostContractFormInformation::E_TENDER_WAIVER_OPTION_INTER_COMPANY_TEXT,
            bsNewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS         => bsNewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS_TEXT
        ];

        $eAuctionWaiverStatuses = [
            bsNewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_SITE_URGENCY  => bsNewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_SITE_URGENCY_TEXT,
            bsNewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_INTER_COMPANY => bsNewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_INTER_COMPANY_TEXT,
            bsNewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS        => bsNewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS_TEXT
        ];

        return View::make('dashboard.index', compact(
            'user',
            'countries',
            'selectedCountry',
            'months',
            'selectedFromMonth',
            'selectedToMonth',
            'years',
            'selectedFromYear',
            'selectedToYear',
            'procurementMethods',
            'projectStatuses',
            'eTenderWaiverStatuses',
            'eAuctionWaiverStatuses',
            'dashboardType',
            'formRoute'
        ));
    }

    public function getProcurementMethodAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user    = Confide::user();
        $country = Country::find($countryId);

        $data = [];

        if($country)
        {
            $years = range(date('Y'), $user->dashboardGroup()->getMinYear($country));

            $procurementMethodSummary = $user->getProcurementMethodSummary($country, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);

            $sumRecords = [];

            foreach($procurementMethodSummary as $subsidiaryId => $summary)
            {
                $info = [
                    'id' => $subsidiaryId,
                    'name' => $summary['name']
                ];

                foreach($summary['procurement_methods'] as $procurementMethodId => $record)
                {
                    $info[$procurementMethodId.'_total'] = $record['total'];

                    if(!array_key_exists($procurementMethodId, $sumRecords))
                    {
                        $sumRecords[$procurementMethodId] = [
                            'name' => $record['procurement_method_name'],
                            'total' => 0
                        ];
                    }

                    $sumRecords[$procurementMethodId]['total'] += $record['total'];
                }

                $data[] = $info;
            }

            if(!empty($sumRecords))
            {
                $last = [
                    'id' => 'last-row',
                    'name' => trans('projects.total')
                ];

                foreach($sumRecords as $id => $info)
                {
                    $last[$id.'_procurement_method_name'] = $info['name'];
                    $last[$id.'_total'] = $info['total'];
                }

                $data[] = $last;
            }
        }

        return Response::json($data);
    }

    public function getProjectStatusAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user    = Confide::user();
        $country = Country::find($countryId);

        $data = [];

        if($country)
        {
            $years = range(date('Y'), $user->dashboardGroup()->getMinYear($country));

            $projectStatusSummary = $user->getProjectStatusSummary($country, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);

            $sumRecords = [];

            foreach($projectStatusSummary as $summary)
            {
                $record = [
                    'id' => $summary['id'],
                    'name' => $summary['name']
                ];

                foreach($summary['status'] as $status)
                {
                    $record[$status['id'].'_total'] = $status['total'];

                    if(!array_key_exists($status['id'], $sumRecords))
                    {
                        $sumRecords[$status['id']] = 0;
                    }

                    $sumRecords[$status['id']] += $status['total'];
                }

                $data[] = $record;
            }

            if(!empty($sumRecords))
            {
                $last = [
                    'id' => 'last-row',
                    'name' => trans('projects.total')
                ];

                foreach($sumRecords as $id => $val)
                {
                    $last[$id.'_status_txt'] = Project::getStatusText($id);
                    $last[$id.'_total'] = $val;
                }

                $data[] = $last;
            }
        }
        
        return Response::json($data);
    }

    public function getETenderWaiverStatusAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user    = Confide::user();
        $country = Country::find($countryId);

        $data = [];

        $records = [];

        if($country)
        {
            $years = range(date('Y'), $user->dashboardGroup()->getMinYear($country));

            $eTenderWaiverStatusSummary = $user->getETenderWaiverStatusSummary($country, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);

            $sumRecords = [];

            foreach($eTenderWaiverStatusSummary as $id => $summary)
            {
                $record = [
                    'id' => $id,
                    'name' => $summary['name']
                ];

                $overallTotal = 0;

                foreach($summary['waiver_option_types'] as $waiverTypeVal => $waiverType)
                {
                    $record[$waiverTypeVal."_total"] = $waiverType['total'];

                    $overallTotal += $waiverType['total'];

                    if(!array_key_exists($waiverTypeVal, $sumRecords))
                    {
                        $sumRecords[$waiverTypeVal] = 0;
                    }

                    $sumRecords[$waiverTypeVal] += $waiverType['total'];
                }

                $record["overall_sum"] = $overallTotal;

                $records[] = $record;
            }

            if(!empty($records))
            {
                $last = [
                    'id' => 'last-row',
                    'name' => trans('projects.total')
                ];

                $overallTotal = 0;

                foreach($sumRecords as $id => $val)
                {
                    $last[$id.'_status_txt'] = bsNewPostContractFormInformation::getWaiverTypeText($id);
                    $last[$id.'_total'] = $val;
                    $overallTotal += $val;
                }

                $last["overall_sum"] = $overallTotal;

                $records[] = $last;
            }
        }

        return Response::json($records);
    }

    public function getETenderWaiverStatusOtherAjax($subsidiaryId, $countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $user       = Confide::user();
        $subsidiary = Subsidiary::find($subsidiaryId);
        $country    = Country::find($countryId);

        $summaries = [];

        if($subsidiary && $country)
        {
            $years = range(date('Y'), $user->dashboardGroup()->getMinYear($country));

            $summaries = $user->getETenderWaiverOtherStatusDetails($subsidiary, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);
        }

        return Response::json($summaries);
    }

    public function getEAuctionWaiverStatusOtherAjax($subsidiaryId, $countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $user       = Confide::user();
        $subsidiary = Subsidiary::find($subsidiaryId);
        $country    = Country::find($countryId);

        $summaries = [];

        if($subsidiary && $country)
        {
            $years = range(date('Y'), $user->dashboardGroup()->getMinYear($country));

            $summaries = $user->getEAuctionWaiverOtherStatusDetails($subsidiary, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);
        }

        return Response::json($summaries);
    }

    public function getEAuctionWaiverStatusAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user    = Confide::user();
        $country = Country::find($countryId);

        $data = [];

        $records = [];

        if($country)
        {
            $years = range(date('Y'), $user->dashboardGroup()->getMinYear($country));

            $eAuctionWaiverStatusSummary = $user->getEAuctionWaiverStatusSummary($country, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);

            $sumRecords = [];

            foreach($eAuctionWaiverStatusSummary as $id => $summary)
            {
                $record = [
                    'id' => $id,
                    'name' => $summary['name']
                ];

                $overallTotal = 0;

                foreach($summary['waiver_option_types'] as $waiverTypeVal => $waiverType)
                {
                    $record[$waiverTypeVal."_total"] = $waiverType['total'];

                    $overallTotal += $waiverType['total'];

                    if(!array_key_exists($waiverTypeVal, $sumRecords))
                    {
                        $sumRecords[$waiverTypeVal] = 0;
                    }

                    $sumRecords[$waiverTypeVal] += $waiverType['total'];
                }

                $record["overall_sum"] = $overallTotal;

                $records[] = $record;
            }

            if(!empty($records))
            {
                $last = [
                    'id' => 'last-row',
                    'name' => trans('projects.total')
                ];

                $overallTotal = 0;

                foreach($sumRecords as $id => $val)
                {
                    $last[$id.'_status_txt'] = bsNewPostContractFormInformation::getWaiverTypeText($id);
                    $last[$id.'_total'] = $val;
                    $overallTotal += $val;
                }

                $last["overall_sum"] = $overallTotal;

                $records[] = $last;
            }
        }

        return Response::json($records);
    }

    public function getSubsidiariesAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $country = Country::find($countryId);

        $user        = Confide::user();
        $currentPage = array_key_exists('page', $inputs) ? $inputs['page'] : 0;
        $pageSize    = array_key_exists('size', $inputs) ? $inputs['size'] : 0;

        $years = range(date('Y'), $user->dashboardGroup()->getMinYear($country));

        $info = [];

        if($country)
        {
            $filters = [];

            if(array_key_exists('filters', $inputs))
            {
                foreach($inputs['filters'] as $filter)
                {
                    $filters[$filter['field']] = $filter['value'];
                }
            }

            $info = $user->getDashboardSubsidiaries($country, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ], $filters, $currentPage, $pageSize);
        }

        return Response::json($info);
    }

    public function getSubsidiariesDashboardBAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user        = Confide::user();
        $country     = Country::find($countryId);

        $info = [];

        $years = ($country) ? range(date('Y'), $user->dashboardGroup()->getMinYear($country)) : [date('Y')];

        $subsidiaryIds = (array_key_exists('ids', $inputs)) ? $inputs['ids'] : [];
        if($country && is_array($subsidiaryIds) && !empty($subsidiaryIds))
        {
            $info = $user->getDeveloperDashboardBData($country, $subsidiaryIds, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);
        }

        return Response::json($info);
    }

    public function getSubsidiariesDashboardCAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user        = Confide::user();
        $country     = Country::find($countryId);

        $info = [];

        $years = ($country) ? range(date('Y'), $user->dashboardGroup()->getMinYear($country)) : [date('Y')];

        $subsidiaryIds = (array_key_exists('ids', $inputs)) ? $inputs['ids'] : [];
        if($country && is_array($subsidiaryIds) && !empty($subsidiaryIds))
        {
            $info = $user->getSubsidiariesSavingOrOverRunByWorkCategories($country, $subsidiaryIds, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);
        }

        $results = [];

        foreach($info as $records)
        {
            $data = [
                'name' => $records['name'],
            ];

            foreach($records['work_categories'] as $workCategoryId => $record)
            {
                $data[$workCategoryId.'_overrun_amount']     = $record['overrun_amount'];
                $data[$workCategoryId.'_overrun_percentage'] = $record['overrun_percentage'];
            }

            $results[] = $data;
        }

        unset($info);

        return Response::json($results);
    }

    public function getSubsidiariesDashboardDAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user        = Confide::user();
        $country     = Country::find($countryId);

        $subsidiaryIds  = (array_key_exists('ids', $inputs)) ? $inputs['ids'] : [];
        $workCategories = [];

        $results = [];

        $years = ($country) ? range(date('Y'), $user->dashboardGroup()->getMinYear($country)) : [date('Y')];

        if($country && is_array($subsidiaryIds) && !empty($subsidiaryIds))
        {
            $info = $user->getSubsidiariesSavingOrOverRunByWorkCategories($country, $subsidiaryIds, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);

            $projects = $user->getDashboardProjects($country, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ]);

            $projectIds = (!empty($projects)) ? array_column($projects->toArray(), 'id') : [-1];

            unset($projects);

            $workCategories = WorkCategory::join('projects AS p', 'p.work_category_id', '=', 'work_categories.id')
                ->join('subsidiaries AS s', 's.id', '=', 'p.subsidiary_id')
                ->join('states AS st', 'st.id', '=', 'p.state_id')
                ->whereIn('s.id', $subsidiaryIds)
                ->where('st.country_id', $country->id)
                ->whereIn('p.id', $projectIds)
                ->whereNull('p.deleted_at')
                ->select('work_categories.id AS id', 'work_categories.name AS name')
                ->groupBy('work_categories.id')
                ->distinct()
                ->get()
                ->keyBy('id')
                ->toArray();

            foreach($info as $records)
            {
                $data = [
                    'name'            => $records['name'],
                    'work_categories' => []
                ];

                foreach($records['work_categories'] as $workCategoryId => $record)
                {
                    if(array_key_exists($workCategoryId, $workCategories))
                    {
                        $records['work_categories'][$workCategoryId]['name'] = $workCategories[$workCategoryId]['name'];

                        $data['work_categories'][] = $records['work_categories'][$workCategoryId];
                    }
                }

                $results[] = $data;
            }
        }

        return Response::json($results);
    }

    public function getSubsidiariesDashboardEYearsAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user = Confide::user();
        $country = Country::find($countryId);
        $subsidiaryIds = (array_key_exists('ids', $inputs)) ? $inputs['ids'] : [];

        $years = [];

        $filterYears = ($country) ? range(date('Y'), $user->dashboardGroup()->getMinYear($country)) : [date('Y')];

        if($country && is_array($subsidiaryIds) && !empty($subsidiaryIds))
        {
            $result = $user->getOverallCertifiedPaymentBySubsidiaries($country, $subsidiaryIds, [
                'month' => $fromMonth,
                'year'  => $filterYears[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $filterYears[$toYear]
            ]);

            if($result)
            {
                $subsidiaries = Subsidiary::whereIn('id', array_keys($result))->get();

                foreach($subsidiaries as $subsidiary)
                {
                    $items = $result[$subsidiary->id];

                    foreach($items as $y => $item)
                    {
                        if(!in_array($y, $years))
                        {
                            $years[] = $y;
                        }
                    }
                }
            }
        }

        return Response::json($years);
    }

    public function getSubsidiariesDashboardEAjax($countryId, $year, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user    = Confide::user();
        $country = Country::find($countryId);

        $subsidiaryIds  = (array_key_exists('ids', $inputs)) ? $inputs['ids'] : [];

        $records = [];

        if($country && is_array($subsidiaryIds) && !empty($subsidiaryIds))
        {
            $filterYears = range(date('Y'), $user->dashboardGroup()->getMinYear($country));

            $result = $user->getOverallCertifiedPaymentBySubsidiaries($country, $subsidiaryIds, [
                'month' => $fromMonth,
                'year'  => $filterYears[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $filterYears[$toYear]
            ]);

            if($result)
            {
                $subsidiaries = Subsidiary::whereIn('id', array_keys($result))->get();

                foreach($subsidiaries as $subsidiary)
                {
                    $items = $result[$subsidiary->id];

                    $record = [
                        'id' => $subsidiary->id,
                        'name' => $subsidiary->name,
                        'cost_vs_time' => [],
                        'cumulative_cost' => []
                    ];

                    foreach($items as $y => $item)
                    {
                        if((int)$year > 0 && $year != $y)
                            continue;

                        foreach($item as $month => $data)
                        {
                            $record['cost_vs_time'][] = [
                                date('M y', strtotime($y.'-'.$month.'-01')),
                                $data['total']
                            ];

                            $record['cumulative_cost'][] = [
                                date('M y', strtotime($y.'-'.$month.'-01')),
                                $data['cumulative_cost']
                            ];
                        }
                    }

                    $records[] = $record;
                }
            }
        }

        return Response::json($records);
    }

    public function getMainContractsAjax($countryId, $fromMonth, $fromYear, $toMonth, $toYear)
    {
        $inputs = Input::all();

        $user        = Confide::user();
        $country     = Country::find($countryId);
        $currentPage = array_key_exists('page', $inputs) ? $inputs['page'] : 0;
        $pageSize    = array_key_exists('size', $inputs) ? $inputs['size'] : 0;

        $info = [
            'last_page' => 0,
            'data'      => []
        ];

        if($country)
        {
            $filters = [];

            if(array_key_exists('filters', $inputs))
            {
                foreach($inputs['filters'] as $filter)
                {
                    $filters[$filter['field']] = $filter['value'];
                }
            }

            $years = range(date('Y'), $user->dashboardGroup()->getMinYear($country));

            $info = $user->getDashboardMainContracts($country, [
                'month' => $fromMonth,
                'year'  => $years[$fromYear]
            ],[
                'month' => $toMonth,
                'year'  => $years[$toYear]
            ], $filters, $currentPage, $pageSize);
        }

        return Response::json($info);
    }

    private function eBiddingSubsidiaries($bidModeId)
    {
        $subsidiaries = EBiddingStat::join('projects AS p', 'p.id', '=', 'e_bidding_stats.project_id')
            ->join('subsidiaries AS rs', 'rs.id', '=', 'e_bidding_stats.root_subsidiary_id')
            ->join('subsidiaries AS s', 's.id', '=', 'e_bidding_stats.subsidiary_id')
            ->where('e_bidding_stats.e_bidding_mode_id', $bidModeId)
            ->whereNull('p.deleted_at')
            ->whereNull('s.deleted_at')
            ->select(
                'rs.id',
                'rs.name AS root_subsidiary_name'
            )
            ->orderBy('rs.name', 'asc')
            ->distinct()
            ->get();

        $data = [];
        foreach ($subsidiaries as $subsidiary) {
            $data[] = ['id' => $subsidiary->id, 'name' => $subsidiary->root_subsidiary_name];
        }

        return $data;
    }

    public function getEBiddingSubsidiaries()
    {
        $request = Request::instance();
        $bidMode = EBiddingMode::where('slug', $request->input('bid_mode_slug'))->first();

        if (! $bidMode)
        {   // No bid mode found
            return [];
        }

        return Response::json($this->eBiddingSubsidiaries($bidMode->id));
    }

    public function eBidding()
    {
        $user = Confide::user();

        if (! $user->dashboardGroup(DashboardGroup::TYPE_E_BIDDING))
        {
            \Flash::error(trans('errors.operationIsNotAllowed'));
            return Redirect::route('home.index');
        }

        $bidMode = EBiddingMode::first();

        if (! $bidMode)
        {   // No bid mode found
            \Flash::error(trans('errors.invalidSelection'));
            return Redirect::back();
        }

        $bidModeSelected = $bidMode->slug;
        $bidModeSelections = EBiddingMode::getBidModeSelections();

        $subsidiaryList = $this->eBiddingSubsidiaries($bidMode->id);

        return View::make('dashboard.e_bidding.show', compact(
            'bidModeSelected',
            'bidModeSelections',
            'subsidiaryList'
        ));
    }

    private function updateEBiddingData()
    {
        $now = Carbon::now();

        $eBiddings = EBidding::where('bidding_start_time', '<=', $now->format('Y-m-d H:i:s'))
            ->whereNotNull('lowest_tender_amount')
            ->whereNull('processed_at')
            ->where('status', EBidding::STATUS_APPROVED)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($eBiddings as $eBidding)
        {
            // Check if bidding end time has passed
            $endTime = $eBidding->biddingEndTime();
            if (empty($endTime) || $now->lt($endTime))
            {   // Bidding end time has not passed yet
                continue;   // Skip to next
            }
            // Get the leading bid (lowest/highest)
            $firstRank = $this->eBiddingConsoleRepo->getRankings($eBidding->id, true);
            if (! $firstRank)
            {   // No rankings found
                continue;   // Skip to next
            }

            $project = $eBidding->project;
            if (! $project)
            {   // No project found
                continue;   // Skip to next
            }

            // Create/Update e-bidding stats record
            $eBiddingStat = EBiddingStat::where('e_bidding_id', $eBidding->id)->first();
            if (! $eBiddingStat)
            {   // No stats record found
                $eBiddingStat = new EBiddingStat();
                $eBiddingStat->e_bidding_id = $eBidding->id;
                $eBiddingStat->project_id = $eBidding->project_id;
                $eBiddingStat->currency_code = $project->modified_currency_code;
            }

            $subsidiary = $project->subsidiary;
            if ($subsidiary) {
                $eBiddingStat->subsidiary_id = $subsidiary->id;

                $rootSubsidiary = $subsidiary->getTopParentSubsidiary('root');
                if ($rootSubsidiary) {
                    $eBiddingStat->root_subsidiary_id = $rootSubsidiary->id;
                } else {
                    $eBiddingStat->root_subsidiary_id = null;
                }
            } else {
                $eBiddingStat->subsidiary_id = null;
                $eBiddingStat->root_subsidiary_id = null;
            }

            $eBiddingStat->e_bidding_mode_id = $eBidding->e_bidding_mode_id;
            $eBiddingStat->lowest_tender_amount = $eBidding->lowest_tender_amount;
            $eBiddingStat->budget_amount = $eBidding->budget ?? 0;
            $eBiddingStat->leading_bid_amount = $firstRank->bid_amount;

            $eBiddingStat->started_at = $eBidding->bidding_start_time;
            $eBiddingStat->ended_at = $endTime->format('Y-m-d H:i:s');

            $duration = $eBidding->biddingDuration(false);
            $durationExtended = $eBidding->biddingExtendedTime();

            $eBiddingStat->duration = $duration['total'];
            $eBiddingStat->duration_extended = $durationExtended['total'];

            $eBiddingStat->total_bids = \PCK\EBiddings\EBiddingBid::where('e_bidding_id', $eBidding->id)->count();
            $eBiddingStat->total_bidders = \PCK\EBiddings\EBiddingRanking::where('e_bidding_id', $eBidding->id)->count();

            // Calculate differences
            switch ($eBidding->eBiddingMode->slug) {
                case EBiddingMode::BID_MODE_INCREMENT:  // Increment -> Earnings
                    $tenderDiff = $eBiddingStat->leading_bid_amount - $eBiddingStat->lowest_tender_amount;
                    $budgetDiff = $eBiddingStat->budget_amount > 0 ? $eBiddingStat->leading_bid_amount - $eBiddingStat->budget_amount : 0;
                    break;

                default:    // Decrement -> Savings
                    $tenderDiff = $eBiddingStat->lowest_tender_amount - $eBiddingStat->leading_bid_amount;
                    $budgetDiff = $eBiddingStat->budget_amount > 0 ? $eBiddingStat->budget_amount - $eBiddingStat->leading_bid_amount : 0;
            }

            $eBiddingStat->tender_amount_diff = $tenderDiff;
            $eBiddingStat->budget_amount_diff = $budgetDiff;
            $eBiddingStat->save();

            // Flag e-bidding record as processed
            $eBidding->processed_at = $now->format('Y-m-d H:i:s');
            $eBidding->save();
        }
    }

    public function eBiddingStats()
    {
        $data = [];
        $user = Confide::user();

        if (! $user->dashboardGroup(DashboardGroup::TYPE_E_BIDDING))
        {
            return Response::json([
                'error' => trans('errors.operationIsNotAllowed')
            ]);
        }

        $request = Request::instance();

        if (! empty($request->input('filter_bid_mode')))
        {
            $bidMode = EBiddingMode::where('slug', $request->input('filter_bid_mode'))->first();
        } else {
            $bidMode = EBiddingMode::first();
        }

        if (! $bidMode)
        {   // No bid mode found
            return Response::json([
                'error' => trans('errors.invalidSelection')
            ]);
        }
        $bidModeId = $bidMode->id;

        if ($request->has('filter_subsidiaries')) {
            $subsidiariesFilter = $request->input('filter_subsidiaries');

            // Split the comma-separated string into an array and filter out empty values
            if (is_array($subsidiariesFilter)) {
                $subsidiaryIds = array_filter($subsidiariesFilter, function($value) {
                    return !empty($value);
                });
            } else {
                $subsidiaryIds = array_filter(explode(',', $subsidiariesFilter), function($value) {
                    return !empty($value);
                });
            }
        }
        $selectedSubsidiaryIds = $subsidiaryIds ?? [];

        // Update e-bidding stats data first
        $this->updateEBiddingData();

        // Currency code: Default to MYR (To-do: Make it depend on filter)
        $currencyCode = 'MYR';

        // Get e-bidding stats data for the selected bid mode
        $query = EBiddingStat::join('projects AS p', 'p.id', '=', 'e_bidding_stats.project_id')
            ->join('subsidiaries AS rs', 'rs.id', '=', 'e_bidding_stats.root_subsidiary_id')
            ->join('subsidiaries AS s', 's.id', '=', 'e_bidding_stats.subsidiary_id')
            ->where('e_bidding_stats.e_bidding_mode_id', $bidModeId)
            ->whereNull('p.deleted_at')
            ->whereNull('s.deleted_at');

        if (! empty($selectedSubsidiaryIds)) {
            $query->whereIn('e_bidding_stats.root_subsidiary_id', $selectedSubsidiaryIds);
        }

        $eBiddingStats = $query->select(
                'e_bidding_stats.e_bidding_id',
                'rs.name AS root_subsidiary_name',
                's.name AS subsidiary_name',
                'p.title AS project_title',
                'e_bidding_stats.lowest_tender_amount AS tender_amount',
                'e_bidding_stats.budget_amount',
                'e_bidding_stats.leading_bid_amount',
                'e_bidding_stats.tender_amount_diff',
                'e_bidding_stats.budget_amount_diff',
                'e_bidding_stats.started_at',
                'e_bidding_stats.ended_at',
                'e_bidding_stats.duration',
                'e_bidding_stats.duration_extended',
                'e_bidding_stats.total_bids',
                'e_bidding_stats.total_bidders'
            )
            ->orderBy('e_bidding_stats.id', 'desc')
            ->get();

        $projects = [];
        foreach ($eBiddingStats as $stat)
        {
            $projects[] = [
                'e_bidding_id' => $stat->e_bidding_id,
                'root_subsidiary_name' => $stat->root_subsidiary_name,
                'subsidiary_name' => $stat->subsidiary_name,
                'project_title' => $stat->project_title,
                'tender_amount' => NumberHelper::formatNumber($stat->tender_amount),
                'budget_amount' => $stat->budget_amount > 0 ? NumberHelper::formatNumber($stat->budget_amount) : '-',
                'leading_bid_amount' => NumberHelper::formatNumber($stat->leading_bid_amount),
                'tender_amount_diff' => NumberHelper::formatNumber($stat->tender_amount_diff),
                'budget_amount_diff' => $stat->budget_amount > 0 ? NumberHelper::formatNumber($stat->budget_amount_diff) : '-',
                'started_at' => Carbon::parse($stat->started_at)->format('d/m/Y H:i:s'),
                'ended_at' => Carbon::parse($stat->ended_at)->format('d/m/Y H:i:s'),
                'duration' => DateTime::secondsToDuration($stat->duration),
                'duration_extended' => DateTime::secondsToDuration($stat->duration_extended),
                'total_bids' => NumberHelper::formatNumber($stat->total_bids, 0),
                'total_bidders' => NumberHelper::formatNumber($stat->total_bidders, 0),
            ];
        }

        switch ($bidMode->slug) {
            case EBiddingMode::BID_MODE_INCREMENT:
                $tenderAmountLabel = trans('eBiddingStats.highestTenderAmount');
                $leadingBidAmountLabel = trans('eBiddingStats.highestBidAmount');
                $tenderSavingsLabel = trans('eBiddingStats.tenderEarnings');
                $budgetSavingsLabel = trans('eBiddingStats.budgetEarnings');
                break;

            default:
                $tenderAmountLabel = trans('eBiddingStats.lowestTenderAmount');
                $leadingBidAmountLabel = trans('eBiddingStats.lowestBidAmount');
                $tenderSavingsLabel = trans('eBiddingStats.tenderSavings');
                $budgetSavingsLabel = trans('eBiddingStats.budgetSavings');
        }
        $tenderAmountLabel .= ' ('.$currencyCode.')';
        $leadingBidAmountLabel .= ' ('.$currencyCode.')';
        $tenderSavingsLabel .= ' ('.$currencyCode.')';
        $budgetSavingsLabel .= ' ('.$currencyCode.')';

        // Define table column headers for projects table
        $headers = array();
        $columns = [];
        $columns[] = ['title' => trans('eBiddingStats.subsidiary'), 'field' => 'root_subsidiary_name', 'hozAlign' => 'left'];
        $columns[] = ['title' => trans('eBiddingStats.projectTitle'), 'field' => 'project_title', 'hozAlign' => 'left'];
        $columns[] = ['title' => $tenderAmountLabel, 'field' => 'tender_amount', 'hozAlign' => 'right'];
        $columns[] = ['title' => trans('eBiddingStats.budgetAmount'), 'field' => 'budget_amount', 'hozAlign' => 'right'];
        $columns[] = ['title' => $leadingBidAmountLabel, 'field' => 'leading_bid_amount', 'hozAlign' => 'right'];
        $columns[] = ['title' => $tenderSavingsLabel, 'field' => 'tender_amount_diff', 'hozAlign' => 'right'];
        $columns[] = ['title' => $budgetSavingsLabel, 'field' => 'budget_amount_diff', 'hozAlign' => 'right'];
        $columns[] = ['title' => trans('eBiddingStats.startedAt'), 'field' => 'started_at', 'hozAlign' => 'center'];
        $columns[] = ['title' => trans('eBiddingStats.endedAt'), 'field' => 'ended_at', 'hozAlign' => 'center'];
        $columns[] = ['title' => trans('eBiddingStats.duration'), 'field' => 'duration', 'hozAlign' => 'center', 'width' => 120];
        $columns[] = ['title' => trans('eBiddingStats.durationExtended'), 'field' => 'duration_extended', 'hozAlign' => 'center', 'width' => 120];
        $columns[] = ['title' => trans('eBiddingStats.totalBids'), 'field' => 'total_bids', 'hozAlign' => 'center', 'width' => 100];
        $columns[] = ['title' => trans('eBiddingStats.totalBidders'), 'field' => 'total_bidders', 'hozAlign' => 'center', 'width' => 100];

        foreach ($columns as $column) {
            $headers[] = [
                'width' => $column['width'] ?? 200,
                'title' => $column['title'],
                'field' => $column['field'],
                'hozAlign' => $column['hozAlign'],
                'headerHozAlign' => 'center',
                'headerSort' => false,
            ];
        }

        // Total stats
        $totalTenderAmount = $eBiddingStats->sum('tender_amount');
        $totalLeadingBidAmount = $eBiddingStats->sum('leading_bid_amount');
        $totalTenderDiff = $eBiddingStats->sum('tender_amount_diff');
        $totalBudgetDiff = $eBiddingStats->sum('budget_amount_diff');

        // Projects (table)
        $data[] = [
                'id' => 'projects',
                'options' => $headers,
                'data' => $projects
            ];

        // Totals (bar chart)
        $data[] = [
                'id' => 'totals_bar',
                'options' => [
                    'chart' => [
                        'height' => 350,
                        'type' => 'line',
                    ],
                    'stroke' => [
                        'width' => [0, 0, 0, 0],
                    ],
                    'dataLabels' => [
                        'enabled' => false,
                        'series' => []
                    ],
                    'markers' => [
                        'size' => 0,
                    ],
                    'title' => [
                        'text' => '',
                    ],
                    'xaxis' => [
                        'categories' => ['Total']
                    ],
                    'toolbar' => [
                        'show' => true,
                        'tools' => [
                            'download' => true,
                            'selection' => false,
                            'zoom' => false,
                            'zoomin' => false,
                            'zoomout' => false,
                            'pan' => false,
                            'reset' => false
                        ],
                    ],
                ],
                'data' => [
                    [
                        'name' => $tenderAmountLabel,
                        'type' => 'column',
                        'data' => [$totalTenderAmount]
                    ],
                    [
                        'name' => $leadingBidAmountLabel,
                        'type' => 'column',
                        'data' => [$totalLeadingBidAmount]
                    ],
                    [
                        'name' => $tenderSavingsLabel,
                        'type' => 'column',
                        'data' => [$totalTenderDiff]
                    ],
                    [
                        'name' => $budgetSavingsLabel,
                        'type' => 'column',
                        'data' => [$totalBudgetDiff]
                    ]
                ]
            ];

        // Totals (numbers)
        // Total tender amount
        $data[] = [
                'id' => 'totals_tender_amount',
                'options' => $tenderAmountLabel,
                'data' => NumberHelper::formatNumber($totalTenderAmount)
            ];

        // Total leading bid amount
        $data[] = [
                'id' => 'leading_bid_amount',
                'options' => $leadingBidAmountLabel,
                'data' => NumberHelper::formatNumber($totalLeadingBidAmount)
            ];

        // Savings / Earnings (tender)
        $data[] = [
                'id' => 'tender_amount_diff',
                'options' => $tenderSavingsLabel,
                'data' => NumberHelper::formatNumber($totalTenderDiff)
            ];

        // Savings / Earnings (budget)
        $data[] = [
                'id' => 'budget_amount_diff',
                'options' => $budgetSavingsLabel,
                'data' => NumberHelper::formatNumber($totalBudgetDiff)
            ];

        return Response::json($data);
    }
}
