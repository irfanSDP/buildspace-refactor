<?php namespace PCK\Users;

use Confide;
use DB;
use PCK\Exceptions\InvalidAccessLevelException;

trait VerifyCompanyTrait {

    /**
     * Returns true if the current user can grant the privilege to verify companies.
     *
     * @return bool
     */
    public function canGrantPrivilegeToVerifyCompanies()
    {
        return Confide::user()->isSuperAdmin();
    }

    /**
     * Returns true if the user can verify companies.
     *
     * @return bool
     */
    public function canVerifyCompanies()
    {
        if( $this->isSuperAdmin() )
        {
            return true;
        }

        $results = DB::table('users_company_verification_privileges')->where('user_id', '=', $this->id)->first();

        return $results ? true : false;
    }

    /**
     * Grants the user the privilege to verify registering companies.
     *
     * @param bool $grant TRUE if privilege is to be granted, FALSE if privilege is to be revoked.
     *
     * @return bool|int
     * @throws InvalidAccessLevelException
     */
    public function obtainPrivilegeToVerifyCompanies($grant = true)
    {
        if( ! $this->canGrantPrivilegeToVerifyCompanies() )
        {
            throw new InvalidAccessLevelException('Oops! The current user does not have permission to grant this privilege.');
        }

        if( $grant && ( ! $this->canVerifyCompanies() ) )
        {
            return DB::table('users_company_verification_privileges')->insert(array(
                'user_id'    => $this->id,
                'created_at' => 'now()',
                'updated_at' => 'now()',
            ));
        }
        if( ( ! $grant ) && $this->canVerifyCompanies() )
        {
            return DB::table('users_company_verification_privileges')
                ->where('user_id', '=', $this->id)
                ->delete();
        }

        return true;
    }

}