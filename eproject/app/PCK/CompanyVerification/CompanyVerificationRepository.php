<?php namespace PCK\CompanyVerification;

use PCK\Helpers\DataTables;
use PCK\Users\User;

class CompanyVerificationRepository {

    /**
     * Returns a list of assigned users.
     *
     * @param array $inputs
     *
     * @return array
     */
    public function getAssignedUsers(array $inputs)
    {
        $idColumn = 'users.id';
        $selectColumns = array( $idColumn, 'users.name' );

        $userColumns = array(
            'name'  => 1,
            'email' => 2 );

        $companyColumns = array(
            'name' => 3 );

        $allColumns = array(
            'users'     => $userColumns,
            'companies' => $companyColumns
        );

        $usersTableName = with(new User())->getTable();

        $query = \DB::table("{$usersTableName} as users");

        $datatable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->join('companies', 'companies.id', '=', 'users.company_id')
            ->join('users_company_verification_privileges as p', 'p.user_id', '=', 'users.id');

        $datatable->properties->query->where('companies.confirmed', '=', true);

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $dataArray = array();

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );
            $record = User::find($arrayItem->id);

            $dataArray[] = array(
                'indexNo'        => $indexNo,
                'id'             => $record->id,
                'name'           => $record->name,
                'email'          => $record->email,
                'companyName'    => $record->company->name,
                'route:unassign' => route('users.companies.verification.unassign', array( $record->id )),
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    /**
     * Returns a list of assignable users
     * who are not assigned.
     *
     * @param array $inputs
     *
     * @return array
     */
    public function getAssignableUsers(array $inputs)
    {
        $idColumn = 'users.id';
        $selectColumns = array( $idColumn, 'users.name' );

        $userColumns = array(
            'name'  => 1,
            'email' => 2 );

        $companyColumns = array(
            'name' => 3 );

        $allColumns = array(
            'users'     => $userColumns,
            'companies' => $companyColumns
        );

        $usersTableName = with(new User())->getTable();

        $query = \DB::table("{$usersTableName} as users");

        $datatable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->join('companies', 'companies.id', '=', 'users.company_id')
            ->leftJoin('users_company_verification_privileges as p', 'p.user_id', '=', 'users.id')
            ->whereNull('p.user_id');

        $datatable->properties->query->where('companies.confirmed', '=', true);

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $dataArray = array();

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );
            $record = User::find($arrayItem->id);

            $dataArray[] = array(
                'indexNo'     => $indexNo,
                'id'          => $record->id,
                'name'        => $record->name,
                'email'       => $record->email,
                'companyName' => $record->company->name,
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    /**
     * Grants the users the privilege to verify registering companies.
     *
     * @param array $userIds
     *
     * @return bool
     * @throws \PCK\Exceptions\InvalidAccessLevelException
     */
    public function assign(array $userIds)
    {
        foreach($userIds as $userId)
        {
            User::find($userId)->obtainPrivilegeToVerifyCompanies();
        }

        return true;
    }

    /**
     * Revokes the user's privilege to verify registering companies.
     *
     * @param $userId
     *
     * @return bool|int
     * @throws \PCK\Exceptions\InvalidAccessLevelException
     */
    public function unassign($userId)
    {
        return User::find($userId)->obtainPrivilegeToVerifyCompanies(false);
    }

}