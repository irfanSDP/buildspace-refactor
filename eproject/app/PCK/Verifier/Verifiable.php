<?php

namespace PCK\Verifier;

interface Verifiable {

    /**
     * View for when object is approved.
     *
     * @return string
     */
    public function getOnApprovedView();

    /**
     * View for when object is rejected.
     *
     * @return string
     */
    public function getOnRejectedView();

    /**
     * View for when object is pending.
     *
     * @return string
     */
    public function getOnPendingView();

    /**
     * Route to relevant page.
     *
     * @return string
     */
    public function getRoute();

    /**
     * Data for the email view.
     *
     * @return array
     */
    public function getViewData($locale);

    /**
     * User objects.
     *
     * @return array
     */
    public function getOnApprovedNotifyList();

    /**
     * User objects.
     *
     * @return array
     */
    public function getOnRejectedNotifyList();

    /**
     * A closure for when the all verifiers have approved.
     *
     * @return \Closure
     */
    public function getOnApprovedFunction();

    /**
     * A closure for when the all verifiers have approved.
     *
     * @return \Closure
     */
    public function getOnRejectedFunction();

    /**
     * A closure for when the object has been reviewed.
     *
     * @return \Closure
     */
    public function onReview();

    /**
     * get the customized email subject.
     *
     * @return string
     */
    public function getEmailSubject($locale);

    /**
     * get the id of the user who submitted the form for approval.
     *
     * @return int|null
     */
    public function getSubmitterId();

    /**
     * get module name.
     *
     * @return string
     */
    public function getModuleName();
}