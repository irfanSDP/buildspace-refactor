<?php
class sspmod_buildspace_Auth_Source_Auth extends sspmod_core_Auth_UserPassBase
{
    private $dsn;
    private $username;
    private $password;

    public function __construct($info, $config)
    {
        parent::__construct($info, $config);
        if ( !is_string($config['dsn']) )
        {
                throw new Exception('Missing or invalid dsn option in config.');
        }
        $this->dsn = $config['dsn'];
        if ( !is_string($config['username']) )
        {
                throw new Exception('Missing or invalid username option in config.');
        }
        $this->username = $config['username'];
        if ( !is_string($config['password']) )
        {
                throw new Exception('Missing or invalid password option in config.');
        }
        $this->password = $config['password'];
    }

    protected function login($email, $password)
    {
        $email = strtolower(trim($email));

        /* Connect to the database. */
        $db = new PDO($this->dsn, $this->username, $this->password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        /* With PDO we use prepared statements. This saves us from having to escape
         * the username in the database query.
         */
        $st = $db->prepare("SELECT id, password, allow_access_to_buildspace, account_blocked_status FROM users WHERE email = :email AND confirmed IS TRUE");
        if ( !$st->execute(array( 'email' => $email )) )
        {
                throw new Exception('Failed to query database for user.');
        }
        /* Retrieve the row from the database. */
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ( !$row )
        {
                /* User not found. */
                SimpleSAML_Logger::warning('MyAuth: Could not find user with E-Mail: ' . var_export($email, true) . '.');
                throw new SimpleSAML_Error_Error('WRONGUSERPASS');
        }
        /* Check the password. */
        if ( !password_verify($password, $row['password']) )
        {
                /* Invalid password. */
                SimpleSAML_Logger::warning('MyAuth: Wrong password for user with E-Mail:' . var_export($email, true) . '.');
                throw new SimpleSAML_Error_Error('WRONGUSERPASS');
        }
        // Check if a newer hashing algorithm is available
        if ( password_needs_rehash($row['password'], PASSWORD_DEFAULT) )
        {
                // If so, create a new hash, and replace the old one
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $st = $db->prepare("UPDATE users SET password = :password WHERE email = :email AND confirmed IS TRUE");
                if ( !$st->execute(array( 'password' => $newHash, 'email' => $email )) )
                {
                        throw new Exception('Failed to update user\'s hashed password.');
                }
        }
        /* Check for account blocking permission */
        if ( $row['account_blocked_status'] )
        {
                SimpleSAML_Logger::warning('MyAuth: Account blocked for user with E-Mail:' . var_export($email, true) . '.');
                throw new SimpleSAML_Error_Error('ACCOUNTBLOCKED');
        }

        return [
            'uid'                        => array( $row['id'] ),
            'allow_access_to_buildspace' => array( $row['allow_access_to_buildspace'] )
        ];
    }
}
