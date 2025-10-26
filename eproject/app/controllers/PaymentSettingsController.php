<?php

use PCK\Payment\PaymentSetting;
use PCK\Payment\PaymentSettingRepository;
use PCK\Forms\PaymentSettingForm;
use PCK\Helpers\DBTransaction;
use PCK\Exceptions\ValidationException;

class PaymentSettingsController extends Controller
{
    private $repository;
    private $form;

    public function __construct(PaymentSettingRepository $repository, PaymentSettingForm $form)
    {
        $this->repository = $repository;
        $this->form       = $form;
    }

    public function index()
    {
        return View::make('payments.index');
    }

    public function getAllRecords()
    {
        $records = $this->repository->getAllRecords();

        return Response::json($records);
    }

    public function store()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->form->validate($inputs);
            $this->repository->createNewRecord($inputs);

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $transaction->rollback();
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getErrors();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function update($paymentSettingId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $paymentSetting = PaymentSetting::find($paymentSettingId);

            $this->form->validate($inputs);
            $this->repository->updateRecord($paymentSetting, $inputs);

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $transaction->rollback();
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getErrors();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function delete($paymentSettingId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $paymentSetting = PaymentSetting::find($paymentSettingId);
            $paymentSetting->delete();

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}